<?php

if ( ! defined( 'ABSPATH' ) ) exit;

function gf_pmp_register_membership_step() {
	class Gravity_Flow_PMP_Membership_Step extends Gravity_Flow_Step {
		// step key
		public $_step_type = 'pmp_register_membership';
		
		// admin label
		public function get_label() {
			return 'PMP Membership';
		}
		
		// icon
		public function get_icon_url() {
			return '<i class="fa fa-user-circle" style="color: darkgreen;"></i>';
		}
		
		// is the step complete?
		public function is_complete() {
			$user_id = get_current_user_id();
			
			$current_level = pmpro_getMembershipLevelForUser( $user_id );
			$workflow_entry = $this->get_entry();
			
			if ( !$workflow_entry ) return false;
			
			$Gravity_Flow_API = new Gravity_Flow_API( $workflow_entry['form_id'] );
			
			$step = $Gravity_Flow_API->get_current_step( $workflow_entry );
			
			if ( $step && $step->get_type() == 'pmp_register_membership' ) {
				$new_only = $step->get_setting('pmp_new_memberships_only');
				
				// if only new memberships count
				if ( $new_only ) {
					$start_date = strtotime( $current_level->startdate );
					$entry_date = strtotime( $workflow_entry->date_created );
					
					// Abort if the membership was started before the entry was created.
					if ( $start_date < $entry_date ) return false;
				}
				
				return true;
			}
			
			return false;
		}
		
		// the step will end if this is complete
		public function status_evaluation() {
			return $this->is_complete() ? 'complete' : 'pending';
		}
		
		// when complete
		public function process() {
			return $this->is_complete();
		}
		
		// allow expiring
		public function supports_expiration() {
			return true;
		}
		
		// custom fields
		public function get_settings() {
			$settings = array(
				'title'  => 'Event Payment',
				'fields' => array(
					array(
						'type'     => 'radio',
						'name'     => 'pmp_new_memberships_only',
						'label'    => 'New memberships only',
						'horizontal' => false,
						'choices' => array(
							array( 'label' => 'Only new memberships qualify (started after the workflow was created)', 'value' => '1' ),
							array( 'label' => 'New or existing memberships will qualify.', 'value' => '0' ),
						)
					),
				),
			);
			
			return $settings;
		}
		
		// display step info in "Workflow" sidebar
		public function workflow_detail_box( $form, $args ) {
			printf(
				'<h4>%s (%s)</h4>',
				esc_html($this->get_name()),
				esc_html($this->get_status())
			);
			
			echo '<p>This step will be completed once the user pays and registers to become a member.</p>';
		}
	}
	
	// Register the step
	Gravity_Flow_Steps::register( new Gravity_Flow_PMP_Membership_Step() );
}
if ( did_action( 'gravityflow_loaded' ) ) {
	gf_pmp_register_membership_step();
}else{
	add_action( 'gravityflow_loaded', 'gf_pmp_register_membership_step' );
}


//
function gf_pmp_on_membership_change_check_step( $level_id, $user_id, $cancel_level ) {
	$current_level = pmpro_getLevel( $level_id );
	$workflow_id = (int) get_user_meta( $user_id, 'Workflow', true );
	$workflow_entry = GFAPI::get_entry($workflow_id);
	
	if ( !$workflow_entry ) return;
	
	$Gravity_Flow_API = new Gravity_Flow_API( $workflow_entry['form_id'] );
	
	$step = $Gravity_Flow_API->get_current_step( $workflow_entry );
	
	if ( $step && $step->get_type() == 'pmp_register_membership' ) {
		$new_only = $step->get_setting('pmp_new_memberships_only');
		
		// if only new memberships count
		if ( $new_only ) {
			$start_date = strtotime( $current_level->startdate );
			$entry_date = strtotime( $workflow_entry->date_created );
			
			// Abort if the membership was started before the entry was created.
			if ( $start_date < $entry_date ) return;
		}
		
		$step->end();
		$Gravity_Flow_API->process_workflow( $workflow_entry['id'] );
	}
}
add_action( 'pmpro_after_change_membership_level', 'gf_pmp_on_membership_change_check_step', 20, 3 );