<?php if ( ! defined( 'FW' ) ) die( 'Forbidden' );

class FW_Extension_Pardot_Forms extends FW_Extension_Forms_Form {

	public function _init() {}

	/**
	 * {@inheritdoc}
	 */
	public function get_form_builder_type() {
		return 'form-builder';
	}

	public function get_form_builder_value( $form_id ) {
		$form = $this->get_form_db_data( $form_id );

		return ( empty( $form['form'] ) ? array() : $form['form'] );
	}

	/**
	 * @param $form_id
	 * @param $data
	 * * id - Form id
	 * * form - Builder value
	 * * email_to - Destination email
	 * * [subject_message]
	 * * [success_message]
	 * * [failure_message]
	 *
	 * @return bool
	 * @internal
	 */
	public function _set_form_db_data($form_id, $data) {
		if (!class_exists('_FW_Ext_Pardot_Form_DB_Data')) {
			require_once dirname(__FILE__) .'/includes/helper/class--fw-ext-pardot-form-db-data.php';
		}

		return _FW_Ext_Pardot_Form_DB_Data::set($form_id, $data);
	}

	private function get_form_db_data($form_id) {
		if (!class_exists('_FW_Ext_Pardot_Form_DB_Data')) {
			require_once dirname(__FILE__) .'/includes/helper/class--fw-ext-pardot-form-db-data.php';
		}

		return _FW_Ext_Pardot_Form_DB_Data::get($form_id);
	}

	/**
	 * @param array $data
	 * * id   - form id
	 * * form - builder value
	 * * [submit_button_text]
	 * @param array $view_data
	 * @return string
	 */
	public function render( $data, $view_data = array() ) {
		$form = $data['form'];

		if ( empty( $form ) ) {
			return '';
		}

		$form_id = $data['id'];
		$submit_button_text = empty( $data['submit_button_text'] )
			? __( 'Submit', 'fw' )
			: $data['submit_button_text'];

		/**
		 * @var FW_Extension_Forms $forms_extension
		 */
		$forms_extension = fw_ext( 'pardot' );

		return $this->render_view(
			'form',
			array(
				'form_id'   => $form_id,
				'form_html' => $forms_extension->render_form(
					$form_id,
					$form,
					$this->get_name(),
					$this->render_view(
						'submit',
						array(
							'submit_button_text' => $submit_button_text,
							'form_id' => $form_id,
							'extra_data' => $view_data,
						)
					),
					$data['pardot_link']
				),
				'extra_data' => $view_data,
			)
		);
	}

	public function process_form( $form_values, $data ) {

	}

	/**
	 * @internal
	 */
	public function _action_post_form_type_save() {

	}

	/**
	 * Returns value of the form option
	 *
	 * @param string $id
	 * @param null|string $multikey
	 *
	 * @return mixed|null
	 */
	public function get_option( $id, $multikey = null ) {
		$form = $this->get_form_db_data( $id );

		if ( empty( $form ) ) {
			return null;
		}

		if ( is_null( $multikey ) ) {
			return $form;
		}

		return fw_akg( $multikey, $form );
	}
}
