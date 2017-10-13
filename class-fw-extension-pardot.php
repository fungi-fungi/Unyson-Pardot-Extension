<?php if ( ! defined( 'FW' ) ) {
	die( 'Forbidden' );
}

/** Sub extensions will extend this class */
require dirname( __FILE__ ) . '/includes/extends/class-fw-extension-pardot-form.php';

/**
 * Build frontend forms
 */
class FW_Extension_Pardot extends FW_Extension {

	/**
	 * Via this form will be rendered, validated and saved forms on frontend
	 * @var FW_Form
	 */
	private $frontend_form;

	/**
	 * @internal
	 */
	protected function _init() {
		$this->frontend_form = new FW_Form( 'fw_pardot_form', array(
			'render'   => array( $this, '_frontend_form_render' ),
			'validate' => array( $this, '_frontend_form_validate' ),
			'save'     => array( $this, '_frontend_form_save' ),
		) );

		add_filter('fw:form:nonce-name-data', array($this, '_filter_frontend_nonce_name_date'), 10, 3);
	}

	/**
	 * Render from items
	 *
	 * @param string $form_id
	 * @param array $form
	 * @param string $form_type
	 * @param string $submit_button
	 *
	 * @return string
	 */
	public final function render_form( $form_id, $form, $form_type, $submit_button = null, $pardot_link = null ) {

		if ( empty( $form['json'] ) ) {
			return '';
		}

		ob_start();
		{
			$this->frontend_form->render( array(
				'builder_value' => json_decode( $form['json'], true ),
				'form_type'     => $form_type,
				'form_id'       => $form_id,
				'submit'        => $submit_button,
				'action'        => $pardot_link
			) );
		}

		return ob_get_clean();
	}

	/**
	 * @internal
	 *
	 * @param array $data
	 *
	 * @return array
	 */
	public function _frontend_form_render( $data ) {
		$form_id              = $data['data']['form_id'];
		$form_type            = $data['data']['form_type'];
		$submit_button        = $data['data']['submit'];
		$form_type_input_name = 'fw_ext_forms_form_type';
		$form_type_input_id   = 'fw_ext_forms_form_id';
		$recaptcha_key = jevelin_option('recaptcha_key', false);

		$data['attr']['data-fw-ext-forms-type'] = $form_type;
		$data['attr']['class'] = apply_filters('fw:ext:forms:attr:class', $data['attr']['class']);

		echo '<input type="hidden" name="' . $form_type_input_name . '" value="' . esc_attr( $form_type ) . '" />';
		echo '<input type="hidden" name="' . $form_type_input_id . '" value="' . esc_attr( $form_id ) . '" />';

		echo '<div id="recaptcha" class="g-recaptcha" data-sitekey="' . $recaptcha_key . '" data-callback="onSubmit'.$data['data']['form_id'].'" data-size="invisible"></div>';

		/**
		 * @var FW_Ext_Forms_Type $form_type
		 */
		$form_type = fw_ext( $form_type );

		/**
		 * @var FW_Option_Type_Form_Builder $builder
		 */
		$builder = fw()->backend->option_type( $form_type->get_form_builder_type() );

		echo $builder->frontend_render( $data['data']['builder_value'], FW_Request::POST() );

		if ( ! is_null( $submit_button ) ) {
			$data['submit']['html'] = $submit_button;
		}


		return $data;
	}

	/**
	 * @internal
	 *
	 * @param array $errors
	 *
	 * @return array
	 */
	public function _frontend_form_validate( $errors ) {

		return $errors;
	}

	/**
	 * @param array $fw_form_data
	 *
	 * @return array
	 *
	 * @internal
	 */
	public function _frontend_form_save( $fw_form_data ) {
		
		return $fw_form_data;
	}

	/**
	 * @internal
	 *
	 * @param mixed $child_extension_instance
	 *
	 * @return bool
	 */
	public function _child_extension_is_valid( $child_extension_instance ) {
		return is_subclass_of( $child_extension_instance, 'FW_Extension_Pardot_Form' );
	}

	/**
	 * Extract recursive all items in one level array
	 * @param array $extracted {shortcode => item}
	 * @param $items array, some items can have sub-items in the '_items' key
	 */
	private function extract_shortcode_item(&$extracted, &$items)
	{
		if (!is_array($items)) {
			return;
		}
		foreach ($items as &$item) {
			$extracted[ $item['shortcode'] ] = $item;
			if (!empty($item['_items'])) {
				$this->extract_shortcode_item($extracted, $item['_items']);
			}
		}
	}

	/**
	 * @param string $val
	 * @param FW_Form $form
	 * @param array $render_data
	 * @return string
	 */
	public function _filter_frontend_nonce_name_date($val, $form, $render_data) {
		if ($form->get_id() === $this->frontend_form->get_id()) {
			if (isset($render_data['data']['form_id'])) {
				return $render_data['data']['form_id'];
			} else {
				return FW_Request::POST('fw_ext_pardot_form_id', '');
			}
		}

		return $val;
	}
}
