<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

use Cbx\Petition\CBXSetting;
use Cbx\Petition\Helpers\PetitionHelper;



if ( ! class_exists( 'CBXPetitionNewSignAdminAlertEmail', false ) ) :

	/**
	 * Class CBXPetitionEmailEmailReviewAdminAlert file
	 *
	 * Sending email alert to admin when user signs a petition
	 */
	class CBXPetitionNewSignAdminAlertEmail extends CBXPetitionEmail {
		/**
		 * Constructor.
		 */
		public function __construct() {
			$this->id          = 'new_sign_admin_alert';
			$this->user_email  = false; //alert for admin
			$this->title       = esc_html__( 'New sign admin email alert', 'cbxpetition' );
			$this->description = esc_html__( 'Sends notification to admin on new sign.', 'cbxpetition' );

			$this->template_html = 'emails/new_sign_admin_alert.php';

			$this->placeholders = [
				'{petition}'             => '', //html
				'{petition_id}'          => '',
				'{petition_title}'       => '',
				'{signature_first_name}' => '',
				'{signature_last_name}'  => '',
				'{signature_email}'      => '',
				'{signature_comment}'    => '',
				'{signature_id}'         => '',
				'{signature_count}'      => '',
				'{signature_status}'     => '',
				'{signature_edit_url}'   => '' //html
			];

			// Triggers for this email.
			add_action( 'cbxpetition_sign_submit_after_insert', [ $this, 'trigger' ], 10, 3 );

			// Call parent constructor.
			parent::__construct();

			// Other settings.
			$this->recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) );
		}//end method constructor

		/**
		 * Initialise Settings Form Fields - these are generic email options most will use.
		 */
		public function init_form_fields() {
			/* translators: %s: list of placeholders */
			$placeholder_text  = sprintf( __( 'Available placeholders: %s', 'cbxpetition' ), '<code>' . esc_html( implode( '</code>, <code>', array_keys( $this->placeholders ) ) ) . '</code>' );
			$this->form_fields = [
				'enabled'            => [
					'title'   => esc_html__( 'Enable/Disable', 'cbxpetition' ),
					'type'    => 'checkbox',
					'label'   => esc_html__( 'Enable this email notification', 'cbxpetition' ),
					'default' => 'yes'
				],
				'recipient'          => [
					'title'       => esc_html__( 'Recipient(s)', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => esc_html__( 'Email Recipient(s). Put multiple as comma.', 'cbxpetition' ),
					'placeholder' => esc_html__( 'Email', 'cbxpetition' ),
					'default'     => $this->get_default_recipient()
				],
				'subject'            => [
					'title'       => esc_html__( 'Subject', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => esc_html__( 'Email subject here', 'cbxpetition' ),
					'default'     => $this->get_default_subject()
				],
				'heading'            => [
					'title'       => esc_html__( 'Email heading', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => $placeholder_text,
					'placeholder' => esc_html__( 'Email heading here', 'cbxpetition' ),
					'default'     => $this->get_default_heading()
				],
				'additional_content' => [
					'title'       => esc_html__( 'Additional content', 'cbxpetition' ),
					'description' => esc_html__( 'Text to appear below the main email content.', 'cbxpetition' ) . ' ' . $placeholder_text,
					'css'         => 'width:400px; height: 75px;',
					'placeholder' => esc_html__( 'N/A', 'cbxpetition' ),
					'type'        => 'textarea',
					'default'     => $this->get_default_additional_content(),
					'desc_tip'    => true
				],
				'email_type'         => [
					'title'       => esc_html__( 'Email type', 'cbxpetition' ),
					'type'        => 'select',
					'description' => esc_html__( 'Choose which format of email to send.', 'cbxpetition' ),
					'default'     => 'html',
					'class'       => 'email_type',
					'options'     => $this->get_email_type_options(),
					'desc_tip'    => true
				],
				'from_name'          => [
					'title'       => esc_html__( 'From Name', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => esc_html__( 'Email sent from name. Put empty to set this from WordPress core or via any smtp plugin.', 'cbxpetition' ),
					'placeholder' => esc_html__( 'From name', 'cbxpetition' ),
					'default'     => ''
				],
				'from_email'         => [
					'title'       => esc_html__( 'From Email', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => esc_html__( 'Email sent from name. Put empty to set this from WordPress core or via any smtp plugin.', 'cbxpetition' ),
					'placeholder' => esc_html__( 'From Email', 'cbxpetition' ),
					'default'     => ''
				],
				'cc'                 => [
					'title'       => esc_html__( 'CC', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => esc_html__( 'Email Recipient(s) as CC. Put multiple as comma.', 'cbxpetition' ),
					'placeholder' => esc_html__( 'Email', 'cbxpetition' ),
					'default'     => ''
				],
				'bcc'                => [
					'title'       => esc_html__( 'BCC', 'cbxpetition' ),
					'type'        => 'text',
					'desc_tip'    => true,
					'description' => esc_html__( 'Email Recipient(s) as BCC. Put multiple as comma.', 'cbxpetition' ),
					'placeholder' => esc_html__( 'Email', 'cbxpetition' ),
					'default'     => ''
				],
			];

		}//end method init_form_fields

		/**
		 * Trigger the sending of this email
		 *
		 * @param $petition_id
		 * @param $log_id
		 * @param $log_data
		 *
		 * @return void
		 * @since 2.0.0
		 */
		public function trigger( $petition_id, $log_id, $log_data ) {
			$this->object = $log_data;

			if ( $this->is_enabled() && $this->get_recipient() ) {
				$sign_status = PetitionHelper::getPetitionSignStates();

				//$id                = absint( $log_data['id'] );
				$petition_sign_url = admin_url( 'edit.php?post_type=cbxpetition&page=cbxpetition-signatures&view=addedit&id=' . $log_id );

				/* translators: %s: petition sign url */

				$petition_sign_url_formatted = sprintf( '<a href="%s">' . wp_kses( __( 'To edit click this url.', 'cbxpetition' ), [] ) . '</a>', esc_url( $petition_sign_url ) );

				//petition related
				$this->placeholders['{petition}']        = '<a href="' . esc_url( get_permalink( $petition_id ) ) . '">' . get_the_title( $petition_id ) . '</a>';
				$this->placeholders['{petition_id}']     = $petition_id;
				$this->placeholders['{petition_title}']  = get_the_title( $petition_id );
				$this->placeholders['{signature_count}'] = cbxpetition_signature_count( $petition_id );

				//signature related
				$this->placeholders['{signature_first_name}'] = $log_data['f_name'];
				$this->placeholders['{signature_last_name}']  = $log_data['l_name'];
				$this->placeholders['{signature_email}']      = $log_data['email'];
				$this->placeholders['{signature_comment}']    = $log_data['comment'];
				$this->placeholders['{signature_id}']         = $log_id;
				$this->placeholders['{signature_status}']     = $sign_status[ $log_data['state'] ] ?? '';

				//signature and admin scope related
				$this->placeholders['{signature_edit_url}'] = $petition_sign_url_formatted;


				$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() );
			}
		}//end method trigger

		/**
		 * Get email subject.
		 *
		 * @return string
		 * @since  3.1.0
		 */
		public function get_default_subject() {
			return esc_html__( 'New Petition Signature', 'cbxpetition' );
		}//end method get_default_subject

		/**
		 * Get email heading.
		 *
		 * @return string
		 * @since  3.1.0
		 */
		public function get_default_heading() {
			return esc_html__( 'New Petition Signature', 'cbxpetition' );
		}//end method get_default_heading

		/**
		 * Default content to show below main email content.
		 *
		 * @return string
		 * @since 3.7.0
		 */
		public function get_default_additional_content() {
			return '';
		}//end method get_default_additional_content

		/**
		 * Get email content.
		 *
		 * @return string
		 */
		public function get_content() {
			//$this->sending = true;

			if ( 'plain' === $this->get_email_type() ) {
				$email_content = wordwrap( preg_replace( $this->plain_search, $this->plain_replace, wp_strip_all_tags( $this->get_content_plain() ) ), 70 );
			} else {
				$email_content = $this->get_content_html();
			}

			return $email_content;
		}//end method get_content

		/**
		 * Get content html.
		 *
		 * @return string
		 */
		public function get_content_html() {
			return cbxpetition_get_template_html( $this->template_html, [
				'email_heading'      => $this->get_heading(),
				'additional_content' => $this->get_additional_content(),
				'email'              => $this,
			] );
		}//end method get_content_html

		/**
		 * Get content plain.
		 *
		 * @return string
		 */
		public function get_content_plain() {
			$message = $this->get_content_html();

			return \Soundasleep\Html2Text::convert( $message );
		}//end method get_content_plain
	}//end class CBXPetitionNewSignAdminAlertEmail
endif;

return new CBXPetitionNewSignAdminAlertEmail();