<?php

namespace Symforce\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class DashboardController extends Controller
{
    
    /**
     * @return \Symforce\AdminBundle\Compiler\Loader\AdminLoader
     */
    private function getLoader() {
        return $this->get('sf.admin.loader') ;
    }

    private function getDutyCount(array & $tree, array & $duty_count, \Symforce\AdminBundle\Compiler\Loader\AdminLoader $loader){
        
        foreach($tree as $admin_name => $child ) {
            $admin  = $loader->getAdminByName($admin_name) ;
            
            if( $admin->workflow ) {
                foreach($admin->workflow['status'] as $step_name => $step ) {
                    if( ! $step['duty'] ) {
                        continue ;
                    }
                    if( $step['role'] && ! $this->get('security.context')->isGranted(  $step['role'] ) ) {
                        continue ;
                    }
                    // count the status
                    $count  = $admin->getRouteWorkflowCount( $step_name ) ;
                    if( $count < 1 ){
                        continue ;
                    }
                    $duty_count[ $admin_name ][ $step_name ] = $count ;
                }
                
            }
            
            if( $child ) {
                $this->getDutyCount( $child, $duty_count, $loader ) ;
            }
        }
    }

    /**
     * @Route("/", name="sf_admin_dashboard")
     * @Template()
     */
    public function indexAction(Request $request)
    {
        $loader = $this->getLoader() ;
        
        $tree   = $loader->getAdminTree() ;
        $duty_tasks   = array() ;
        
        $this->getDutyCount($tree, $duty_tasks, $loader );
       
        return array(
            'sf_admin_loader' => $loader ,
            'admin' => null ,
            'action' => null ,
            'duty_tasks' => $duty_tasks ,
            'dashboard_groups' => $loader->getDashboard() ,
        );
    }
    
    /**
     * @Route("/workflow/{admin_name}/{target}/{id}", name="sf_admin_workflow_action")
     */
    public function workflowAction(Request $request, $admin_name, $target, $id )
    {
        $admin = $this->getAdminByName( $admin_name ) ;
        $object = $admin->getObjectById($id) ;
        
        $json   = array(
            'id'    => $id ,
            'error' => null , 
            'message'   => null ,
            'refresh'   => false ,
            'removed'   => false 
        );
        
        if( $object ) {
            if( ! $admin->auth('update', $object) ) {
                $json['error']  = sprintf('auth error', $id ) ;
            } else {
                $status     = $admin->getObjectWorkflowStatus($object) ;
                if( !in_array($target, $status['target']) ) {
                    $json['error']  = sprintf('workflow error', $id ) ;
                } else {
                    $prop  = $admin->getReflectionProperty( $admin->workflow['property'] ) ;
                    $new_status = $admin->workflow['status'][ $target ] ;
                    $new_value  = $new_status['value'] ; 
                     
                    $prop->setValue($object, $new_value ) ;
                    
                    $admin->onWorkflowStatusChange( $object, $target, $status['name'] );
                    
                    $json['refresh']    = true ;
                    
                    $tr     = $this->container->get('translator') ; 
                    $msg    = $tr->trans('sf.workflow.action.finish', array(
                        '%object%'  => $admin->string( $object ) ,
                        '%action%'  => $tr->trans( $new_status['action'], array(), $new_status['domain'] ) ,
                    ), 'SymforceAdminBundle' ) ;
                    
                    $request->getSession()->getFlashBag()->add('info', $msg) ;
                    
                    $em = $admin->getManager() ;
                    $em->persist($object);
                    $em->flush();
                }
            }
        } else {
            $json['error']  = sprintf('not exists', $id ) ;
        }
        
        return new Response(json_encode($json), 200 );
    }
    
    /**
     * @param string $name
     * @return \Symforce\AdminBundle\Compiler\Cache\AdminCache
     */
    private function getAdminByName( $name ) {
        return $this->container->get('sf.admin.loader')->getAdminByName( $name ) ;
    }
}
