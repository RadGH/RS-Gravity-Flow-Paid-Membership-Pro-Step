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
			$current_level = pmpro_getMembershipLevelForUser( get_current_user_id() );
			if ( !empty($current_level) ) return true;
			
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
						'label'    => 'Details',
						'name'     => 'gfpmp_message',
						'type'     => 'gfpmp_step_message',
						'callback'  => function() {
							echo '<p>This step will be completed when the user becomes a member of any level.</p>';
						}
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
			
			echo '<p>This step will be completed when the user becomes a member of any level.</p>';
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


// When changing level, trigger the step
function gf_pmp_on_membership_change_check_step( $level_id, $user_id, $cancel_level ) {
	$workflow_id = (int) get_user_meta( $user_id, 'Workflow', true );
	$workflow_entry = GFAPI::get_entry($workflow_id);
	if ( !$workflow_entry ) return;
	
	// Complete the step
	$Gravity_Flow_API = new Gravity_Flow_API( $workflow_entry['form_id'] );
	$step = $Gravity_Flow_API->get_current_step( $workflow_entry );
	
	if ( $step && $step->get_type() == 'pmp_register_membership' ) {
		$step->end();
		$Gravity_Flow_API->process_workflow( $workflow_entry['id'] );
	}
}
add_action( 'pmpro_after_change_membership_level', 'gf_pmp_on_membership_change_check_step', 20, 3 );