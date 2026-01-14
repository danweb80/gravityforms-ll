<?php

GFForms::include_addon_framework();

class GFLeadLoversAddOn extends GFAddOn {

	protected $_version = GF_LEADLOVERS_VERSION;
	protected $_min_gravityforms_version = '1.9';
	protected $_slug = 'gravityforms-ll';
	protected $_path = 'gravityforms-ll/gravityforms-ll.php';
	protected $_full_path = __FILE__;
	protected $_title = 'Gravity Forms - LeadLovers';
	protected $_short_title = 'GFLeadLovers';

	private static $_instance = null;

	/**
	 * Get an instance of this class.
	 *
	 * @return GFLeadLoversAddOn
	 */
	public static function get_instance() {
		if ( self::$_instance == null ) {
			self::$_instance = new GFLeadLoversAddOn();
		}

		return self::$_instance;
	}

	/**
	 * Handles hooks and loading of language files.
	 */
	public function init() {
		parent::init();
		add_filter( 'gform_submit_button', array( $this, 'form_submit_button' ), 10, 2 );
		add_action( 'gform_after_submission', array( $this, 'after_submission' ), 10, 2 );
	}
	public function init_ajax() {
        parent::init_ajax();
        // add tasks or filters here that you want to perform only during ajax requests
		// ###RETOMAR
		// na v0.4 usamos o js para os chamados ajax... nao serão usados nessa versão mas 
		// devem ser reabilitados caso se retome o uso de selects para captura da máquina/sequencia e nivl
		add_action( 'wp_ajax_get_leadlovers_sequence_list', array($this, 'get_leadlovers_sequence_list_ajax_callback'));
		add_action( 'wp_ajax_get_leadlovers_level_list', array($this, 'get_leadlovers_level_list_ajax_callback'));
    }
	
	// # SCRIPTS & STYLES -----------------------------------------------------------------------------------------------

	/**
	 * Return the scripts which should be enqueued.
	 *
	 * @return array
	 */
	public function scripts() {
		$scripts = array(
			array(
				'handle'  => 'gravityforms-ll_js',
				'src'     => $this->get_base_url() . '/js/gravityforms-ll.js',
				// 'version' => $this->_version,
				'version' => date("h:I:s"),
				'deps'    => array( 'jquery' ),
				// 'in_footer' => true,
				// 'strings' => array(
				// 	'first'  => esc_html__( 'First Choice' ),
				// 	'second' => esc_html__( 'Second Choice' ),
				// 	'third'  => esc_html__( 'Third Choice' )
				// ),
				'enqueue' => array(
					'admin_page' => array(),
					// array(
					// 	'admin_page' => array( 'form_settings' ),
					// 	'tab'        => 'gravityforms-ll'
					// )
				)
			),
		);

		return array_merge( parent::scripts(), $scripts );
	}

	/**
	 * Return the stylesheets which should be enqueued.
	 *
	 * @return array
	 */
	public function styles() {
		$styles = array(
			array(
				'handle'  => 'my_styles_css',
				'src'     => $this->get_base_url() . '/css/my_styles.css',
				'version' => $this->_version,
				'enqueue' => array(
					array( 'field_types' => array( 'poll' ) )
				)
			)
		);

		return array_merge( parent::styles(), $styles );
	}


	// # FRONTEND FUNCTIONS --------------------------------------------------------------------------------------------

	/**
	 * Add the text in the plugin settings to the bottom of the form if enabled for this form.
	 *
	 * @param string $button The string containing the input tag to be filtered.
	 * @param array $form The form currently being displayed.
	 *
	 * @return string
	 */
	function form_submit_button( $button, $form ) {
		$settings = $this->get_form_settings( $form );
		if ( isset( $settings['enabled'] ) && true == $settings['enabled'] ) {
			$text   = $this->get_plugin_setting( 'mytextbox' );
			$button = "<div>{$text}</div>" . $button;
		}

		return $button;
	}

	

	// # ADMIN FUNCTIONS -----------------------------------------------------------------------------------------------

	/**
	 * Creates a custom page for this add-on.
	 */
	// public function plugin_page() {
	// 	echo 'This page appears in the Forms menu';
	// }

	/**
	 * Página de Configuração do Plugin.
	 *
	 * @return array
	 */
	public function plugin_settings_fields() {
		return array(
			array(
				'title'  => esc_html__( 'Configurações do LeadLovers', 'gravityforms-ll' ),
				'fields' => array(
					array(
						'name'              => 'gf_leadlovers_token',
						//'id'				=> 'gf_leadlovers_token',
						'description' => 'Insira aqui o token fornecido pela LeadLovers em <a href="https://app.leadlovers.com/settings">configurações do usuário</a>!',
						//'tooltip'           => esc_html__( 'This is the tooltip', 'gravityforms' ),
						'label'             => esc_html__( 'TOKEN Pessoal', 'gravityforms-ll' ),
						'type'              => 'text',
						'class'             => 'small',
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					)
				)
			)
		);
	}
	
	private function render_ll_dynamic_selects_html( $form ) {

		$settings   = $this->get_form_settings( $form );
		$machine_id = (string) rgar($settings, 'gf_leadlovers_machine', '');
		$seq_id     = (string) rgar($settings, 'gf_leadlovers_sequence', '');
		$level_id   = (string) rgar($settings, 'gf_leadlovers_level', '');

		// máquinas (carrega na renderização)
		$machines = $this->get_leadlovers_machine_list(); // retorna array ['value'=>, 'label'=>]

		// sequences/levels: se já houver machine/sequence salvos, pré-carrega para aparecer ao editar
		$sequences = $machine_id ? $this->get_leadlovers_sequence_list($machine_id) : array();
		$levels    = ($machine_id && $seq_id) ? $this->get_leadlovers_level_list($seq_id, $machine_id) : array();
		ob_start(); ?>
		<div style="margin:12px 0;">
			<label style="display:block;margin-bottom:6px;"><strong>Máquina</strong></label>
			<select id="ll_machine_select" class="medium">
				<?php foreach ($machines as $m): ?>
					<option value="<?php echo esc_attr($m['value']); ?>" <?php selected($machine_id, (string)$m['value']); ?>>
						<?php echo esc_html($m['label']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="margin:12px 0;">
			<label style="display:block;margin-bottom:6px;"><strong>Sequência</strong></label>
			<select id="ll_sequence_select" class="medium" <?php echo $machine_id ? '' : 'disabled'; ?>>
				<?php foreach ($sequences as $s): ?>
					<option value="<?php echo esc_attr($s['value']); ?>" <?php selected($seq_id, (string)$s['value']); ?>>
						<?php echo esc_html($s['label']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>

		<div style="margin:12px 0;">
			<label style="display:block;margin-bottom:6px;"><strong>Nível</strong></label>
			<select id="ll_level_select" class="medium" <?php echo ($machine_id && $seq_id) ? '' : 'disabled'; ?>>
				<?php foreach ($levels as $l): ?>
					<option value="<?php echo esc_attr($l['value']); ?>" <?php selected($level_id, (string)$l['value']); ?>>
						<?php echo esc_html($l['label']); ?>
					</option>
				<?php endforeach; ?>
			</select>
		</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Página de configurações dos formulários (Configurações > LeadLovers tab).
	 *
	 * @return array
	 */
	public function form_settings_fields( $form ) {
		return array(
			// ITENS PARA CADASTRO NA MÁQUINA
			array(
				'title'  => esc_html__( 'Configurações de Cadastro na Máquina LeadLovers', 'gravityforms-ll' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Cadastrar na Máquina', 'gravityforms-ll' ),
						'type'    => 'checkbox',
						'name'    => 'machine_register_enable',
						'tooltip' => esc_html__( 'Marque para habilitar o cadastro na Máquina, em seguida preencha os dados abaixo.', 'gravityforms-ll' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Habilitar', 'gravityforms-ll' ),
								'name'  => 'machine_register_enabled',
								'default_value' => false,
							),
						),
					),
					array(
						'type'              => 'hidden',
						'name'              => 'gf_leadlovers_machine',
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'type'              => 'hidden',
						'name'              => 'gf_leadlovers_sequence',
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'type'              => 'hidden',
						'name'              => 'gf_leadlovers_level',
					),
					// Usado somente para renderizar os selects no front... os values são salvos nos campos hidden acima
					array(
					'type' => 'html',
					'name' => 'll_dynamic_selects',
					'html' => $this->render_ll_dynamic_selects_html( $form ),
					),
					// Mapeamento dos campos do form
					array(
						'label' => esc_html__( 'Nome', 'gravityforms-ll' ),
						'description' => esc_html__( 'Selecione o campo correspondete ao Nome', 'gravityforms-ll' ),
						'type'  => 'field_select',
						'name'  => 'gf_name_field',
					),
					array(
						'label' => esc_html__( 'E-mail', 'gravityforms-ll' ),
						'description' => esc_html__( 'Selecione o campo correspondete ao E-mail', 'gravityforms-ll' ),
						'type'  => 'field_select',
						'name'  => 'gf_email_field',
					),
					// Seleção de Tag
					array(
						'label'             => esc_html__( 'Tag', 'gravityforms-ll' ),
						'type'              => 'select',
						'name'              => 'gf_leadlovers_tag',
						'choices' 			=> $this->get_leadlovers_tag_list() //os produtos serão buscados na função no arquivo do plugin
					),
					// Seleção de campo dinâmico do Leadlovers
					array(
						'label'             => esc_html__( 'Campo Dinâmico', 'gravityforms-ll' ),
						'type'              => 'select',
						'name'              => 'gf_leadlovers_dynamic_field_id',
						//'tooltip'           => esc_html__( 'This is the tooltip', 'gravityforms-ll' ),
						'choices' 			=> $this->get_leadlovers_dynamic_fields_list() //os produtos serão buscados na função no arquivo do plugin
					),
					array(
						'label'             => esc_html__( 'Texto do Campo Dinâmico', 'gravityforms-ll' ),
						'type'              => 'text',
						'name'              => 'gf_leadlovers_dynamic_field_text',
						//'tooltip'           => esc_html__( 'O ', 'gravityforms-ll' ),
						// 'class'             => 'medium',
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
					array(
						'label'   => esc_html__( 'Semana do Ano na no texto do Campo Dinâmico', 'gravityforms-ll' ),
						'type'    => 'checkbox',
						'name'    => 'gf_week_dynamic_field_enable',
						'tooltip' => esc_html__( 'Caso habilitado, o sistema verificará a Semana do Ano atual [1 - 53] e adicionará ao fim do Campo Dinâmico acima.', 'gravityforms-ll' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Habilitar a semana do Ano no Campo Dinâmico', 'gravityforms-ll' ),
								'name'  => 'gf_week_dynamic_field_enabled',
								'default_value' => false,
							),
						),
					),
					array(
						'label'    => esc_html__( 'Somar à Semana do Ano', 'gravityforms-ll' ),
						'type'     => 'text',
						'name'     => 'gf_week_dynamic_field_week_plus',
						'tooltip'  => esc_html__( 'Indica o numero de semanas a partir da semana atual', 'gravityforms-ll' ),
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
				),
			),
			// ITENS PARA CADASTRO NO PRODUTO (CURSO)
			array(
				'title'  => esc_html__( 'Opções de Cadastro em Produtos LeadLovers (Cursos)', 'gravityforms-ll' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Cadastrar em um Produto (Curso)', 'gravityforms-ll' ),
						'type'    => 'checkbox',
						'name'    => 'product_register_enable',
						'tooltip' => esc_html__( 'Marque para habilitar o cadastro no Curso abaixo.', 'gravityforms-ll' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Habilitar', 'gravityforms-ll' ),
								'name'  => 'product_register_enabled',
								'default_value' => false,
							),
						),
					),
					array(
						'label'   => esc_html__( 'Cadastrar no Produto:', 'gravityforms-ll' ),
						'type'    => 'select',
						'name'    => 'gf_leadlovers_product_choice',
						//'tooltip' => esc_html__( 'This is the tooltip', 'simpleaddon' ),
						// 'choices' => apply_filters('get_leadlovers_product_list', array()) //os produtos serão buscados na função no arquivo do plugin
						'choices' => $this->get_leadlovers_product_list() //os produtos serão buscados na função no arquivo do plugin
					),
				)
			),
			// ITENS PARA CONFIGURAÇÃO DE NOTIFICAÇÕES POR EMAIL
			array(
				'title'  => esc_html__( 'Opções para notificação', 'gravityforms-ll' ),
				'fields' => array(
					array(
						'label'   => esc_html__( 'Escolha as opções de notificação', 'gravityforms-ll' ),
						'type'    => 'checkbox',
						'name'    => 'gf_notification_email_enable',
						'tooltip' => esc_html__( 'Receba notificações por e-mail.', 'gravityforms-ll' ),
						'choices' => array(
							array(
								'label' => esc_html__( 'Receber notificações', 'gravityforms-ll' ),
								'name'  => 'gf_notification_enabled',
								'default_value' => false,
							),
							array(
								'label' => esc_html__( 'Receber notificações somente se houver algum erro', 'gravityforms-ll' ),
								'name'  => 'gf_only_error_notification',
								'default_value' => false,
							),
						),
					),
					array(
						'label'             => esc_html__( 'Enviar notificações para o E-mail:', 'gravityforms-ll' ),
						'type'              => 'text',
						'name'              => 'gf_notification_email',
						'tooltip'           => esc_html__( 'Use `;´ para inserir mais de um email', 'gravityforms-ll' ),
						'class'             => 'medium',
						//'feedback_callback' => array( $this, 'is_valid_setting' ),
					),
				)
			)
		);
	}

	/**
	 * Define the markup for the my_custom_field_type type field.
	 *
	 * @param array $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 */
	public function settings_my_custom_field_type( $field, $echo = true ) {
		echo '<div>' . esc_html__( 'My custom field contains a few settings:', 'gravityforms-ll' ) . '</div>';

		// get the text field settings from the main field and then render the text field
		$text_field = $field['args']['text'];
		$this->settings_text( $text_field );

		// get the checkbox field settings from the main field and then render the checkbox field
		$checkbox_field = $field['args']['checkbox'];
		$this->settings_checkbox( $checkbox_field );
	}


	// # SIMPLE CONDITION EXAMPLE --------------------------------------------------------------------------------------

	/**
	 * Define the markup for the custom_logic_type type field.
	 *
	 * @param array $field The field properties.
	 * @param bool|true $echo Should the setting markup be echoed.
	 */
	public function settings_custom_logic_type( $field, $echo = true ) {

		// Get the setting name.
		$name = $field['name'];

		// Define the properties for the checkbox to be used to enable/disable access to the simple condition settings.
		$checkbox_field = array(
			'name'    => $name,
			'type'    => 'checkbox',
			'choices' => array(
				array(
					'label' => esc_html__( 'Enabled', 'gravityforms-ll' ),
					'name'  => $name . '_enabled',
				),
			),
			'onclick' => "if(this.checked){jQuery('#{$name}_condition_container').show();} else{jQuery('#{$name}_condition_container').hide();}",
		);

		// Determine if the checkbox is checked, if not the simple condition settings should be hidden.
		$is_enabled      = $this->get_setting( $name . '_enabled' ) == '1';
		$container_style = ! $is_enabled ? "style='display:none;'" : '';

		// Put together the field markup.
		$str = sprintf( "%s<div id='%s_condition_container' %s>%s</div>",
			$this->settings_checkbox( $checkbox_field, false ),
			$name,
			$container_style,
			$this->simple_condition( $name )
		);

		echo $str;
	}

	/**
	 * Build an array of choices containing fields which are compatible with conditional logic.
	 *
	 * @return array
	 */
	public function get_conditional_logic_fields() {
		$form   = $this->get_current_form();
		$fields = array();
		foreach ( $form['fields'] as $field ) {
			if ( $field->is_conditional_logic_supported() ) {
				$inputs = $field->get_entry_inputs();

				if ( $inputs ) {
					$choices = array();

					foreach ( $inputs as $input ) {
						if ( rgar( $input, 'isHidden' ) ) {
							continue;
						}
						$choices[] = array(
							'value' => $input['id'],
							'label' => GFCommon::get_label( $field, $input['id'], true )
						);
					}

					if ( ! empty( $choices ) ) {
						$fields[] = array( 'choices' => $choices, 'label' => GFCommon::get_label( $field ) );
					}

				} else {
					$fields[] = array( 'value' => $field->id, 'label' => GFCommon::get_label( $field ) );
				}

			}
		}

		return $fields;
	}

	/**
	 * Evaluate the conditional logic.
	 *
	 * @param array $form The form currently being processed.
	 * @param array $entry The entry currently being processed.
	 *
	 * @return bool
	 */
	public function is_custom_logic_met( $form, $entry ) {
		if ( $this->is_gravityforms_supported( '2.0.7.4' ) ) {
			// Use the helper added in Gravity Forms 2.0.7.4.

			return $this->is_simple_condition_met( 'custom_logic', $form, $entry );
		}

		// Older version of Gravity Forms, use our own method of validating the simple condition.
		$settings = $this->get_form_settings( $form );

		$name       = 'custom_logic';
		$is_enabled = rgar( $settings, $name . '_enabled' );

		if ( ! $is_enabled ) {
			// The setting is not enabled so we handle it as if the rules are met.

			return true;
		}

		// Build the logic array to be used by Gravity Forms when evaluating the rules.
		$logic = array(
			'logicType' => 'all',
			'rules'     => array(
				array(
					'fieldId'  => rgar( $settings, $name . '_field_id' ),
					'operator' => rgar( $settings, $name . '_operator' ),
					'value'    => rgar( $settings, $name . '_value' ),
				),
			)
		);

		return GFCommon::evaluate_conditional_logic( $logic, $form, $entry );
	}

	// # HELPERS -------------------------------------------------------------------------------------------------------

	/**
	 * The feedback callback for the 'mytextbox' setting on the plugin settings page and the 'mytext' setting on the form settings page.
	 *
	 * @param string $value The setting value.
	 *
	 * @return bool
	 */
	public function is_valid_setting( $value ) {
		return strlen( $value ) < 10;
	}

	// # AFETER SUBMISSION -------------------------------------------------------------------------------------------------------

	/**
	 * Performing a custom action at the end of the form submission process.
	 *
	 * @param array $entry The entry currently being processed.
	 * @param array $form The form currently being processed.
	 */
	public function after_submission( $entry, $form ) 
	{
		$settings = $this->get_form_settings( $form );

		if(rgar($settings, 'machine_register_enabled') || rgar($settings, 'product_register_enabled'))
		{
			// ----------------------------------------------------
			// PREPARAMOS TODAS AS VARIÁVEIS
			// ----------------------------------------------------
			// Pega o token da página de configuração do Plugin (Add-On)
			$token = $this->get_plugin_setting( 'gf_leadlovers_token' );
			//Pega o array com as configurações do Formulário atual
			//Pega os dados da Máquina/Sequência/Nível 
			$machine_id = rgar($settings,'gf_leadlovers_machine');
			$sequence_id = rgar($settings, 'gf_leadlovers_sequence');
			$level_id = rgar($settings, 'gf_leadlovers_level');
			//Pega os campos da entrada $entry (Nome e Email)
			$email_field_id = rgar($settings, 'gf_email_field');
			$email = rgar($entry, $email_field_id); // Email
			// Capturar os valores das entradas do formulário usando as referencias (ids) de campo
			$name_field_id = rgar($settings, 'gf_name_field');
			$nome = rgar($entry, $name_field_id); // Nome
			//Pega o id da tag
			$tag_id = rgar($settings, 'gf_leadlovers_tag');

			// Pega os campos dinâmicos das configurações
			$dynamic_field_id = rgar($settings, 'gf_leadlovers_dynamic_field_id');
			$dynamic_field_text = rgar($settings, 'gf_leadlovers_dynamic_field_text');

			if(rgar($settings, 'gf_week_dynamic_field_enabled'))
			{
				//Prefixo + Semana do ano + week_plus
				$dynamic_field_week = intval(date_i18n( 'W' )) + intval($settings['gf_week_dynamic_field_week_plus']);
				if($dynamic_field_week > 52)
				{
					$dynamic_field_week -= 52;
				}
				$dynamic_field_text .= strval($dynamic_field_week); 
			}
			
			$product_id = rgar($settings, 'gf_leadlovers_product_choice');
			
			$url = 'http://llapi.leadlovers.com/webapi'; // URI base da API da LeadLovers

			// ----------------------------------------------------
			// ----------------------------------------------------
			// CADASTRAR NA MÁQUINA
			// ----------------------------------------------------
			// ----------------------------------------------------
			
			// Se estivier habilitado o cadastro na máquina
			if(rgar($settings, 'machine_register_enabled'))
			{
				// ----------------------------------------------------
				// VAMOS FAZER O POST PARA INSERIR O LEAD NA MAQUINA 
				// ----------------------------------------------------
				$body = array(
					'Name' => $nome,
					'Email' => $email,
					'MachineCode' => $machine_id,
					'EmailSequenceCode' => $sequence_id,
					'SequenceLevelCode' => $level_id,
				);
				if ($tag_id)
				{
					$body = array(
						'Tag' => $tag_id
					);
				}
				if ($dynamic_field_id) 
				{
					$body = array(
						'DynamicFields' => array(
							array('Id' => $dynamic_field_id, 'Value' => $dynamic_field_text)
					));
				}

				$response = wp_remote_post( $url . '/lead' . '?token=' . $token, array(
					'method'      => 'POST',
					'timeout'     => 30,
					'redirection' => 10,
					'httpversion' => '1.0',
					'blocking'    => true,
					'headers'     => array('accept: application/json'),
					'body'        => $body
				));

				// pegamos o "corpo" da resposta recebida...
				$response_body = wp_remote_retrieve_body( $response );
				// e transformamos de JSON em um array PHP normal.
				$response_data = json_decode( $response_body, true );

				switch($response_data['StatusCode'])
				{
					case 200:	// OK!
						$messages = 'Cadastro na Máquina [' . $machine_id . '] OK: '. $response_data['Message'] . '.<br>';
						break;
					case 400:
					case 401:
					default:
						$messages = 'Cadastro na Máquina [' . $machine_id . '] falhou!!! Erro ' . $response_data['StatusCode'] . ': ' . $response_data['Message'] . '.<br>';
						$error = true;

				}
			}

			// ----------------------------------------------------
			// ----------------------------------------------------
			// INSERIR O LEAD NO PRODUTO (CURSO)
			// ----------------------------------------------------
			// ----------------------------------------------------

			if(rgar($settings, 'product_register_enabled'))
			{
				$response = wp_remote_post( $url . '/customer' . '?token=' . $token, array(
				'method'      => 'POST',
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array('accept: application/json'),
				'body'        => array(
					'Name' => $nome,
					'Email' => $email,
					'ProductId' => $product_id
					))
				);
				// pegamos o "corpo" da resposta recebida...
				$response_body = wp_remote_retrieve_body( $response );
				// e transformamos de JSON em um array PHP normal.
				$response_data = json_decode( $response_body, true );

				switch($response_data['StatusCode'])
				{
					case 200:	// OK!
						$messages .= 'Cadastro no Produto [' . $product_id . '] OK: '. $response_data['Message'] . '.<br>';
						break;
					case 400:	// ERRO
					case 401:
					default:
						$messages .= 'Cadastro no Produto [' . $product_id . '] falhou!!! Erro ' . $response_data['StatusCode'] . ': ' . $response_data['Message'] . '.<br>';
						$error = true;

				}
			}
			
			// ----------------------------------------------------
			// NOTIFICAÇÕES POR E-MAIL
			// ----------------------------------------------------

			if (rgar($settings, 'gf_notification_enabled') || rgar($settings, 'gf_only_error_notification'))
			{
				//DEBUG
				// $console = "<script>console.log(' Mensagem do além: Agora vai!!! ');</script>";
				// echo $console;

				$body = 'Nome: ' . $nome . '<br>';
				$body .= 'E-mail: ' . $email . '<br>';
				$body .= '---------------------------------------------------------<br>';
				$body .= $messages;

				if (rgar($settings, 'gf_only_error_notification'))
				{
					if ($error)
					{
						$subject = '[' . $form['title'] . '] ' . 'ERRO no cadastro';
						$to = rgar($settings, 'gf_notification_email');
						$headers = array('Content-Type: text/html; charset=UTF-8');
						wp_mail( $to, $subject, $body, $headers );
					}
				}
				else
				{
					$subject = '[' . $form['title'] . '] ' . 'Notificação';
					$to = rgar($settings, 'gf_notification_email');
					$headers = array('Content-Type: text/html; charset=UTF-8');
					wp_mail( $to, $subject, $body, $headers );

				}
			}
		}
	} // FIM DA FUNÇÃO

		
	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [value] => [label]
	//******************************************************************************
	public function get_leadlovers_tag_list()
	{
		$form = $this->get_current_form();
		// Pega o array com as configurações do Formulário atual
		$settings = $this->get_form_settings( $form );

		$url = 'http://llapi.leadlovers.com/webapi';
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );
		// ----------------------------------------------------
		// VAMOS FAZER UM GET PARA CAPTURAR O ID DA TAG
		// ----------------------------------------------------
		$response = wp_remote_get( $url . '/tags' . '?token=' . $token, array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
			));

		// pegamos o "corpo" da resposta recebida...
		$response_body = wp_remote_retrieve_body( $response );
		// e transformamos de JSON em um array PHP normal.
		$response_data = json_decode( $response_body, true);

		if (!is_wp_error( $response ))
		{
			//capturamos as tags da resposta
			$items = $response_data['Tags'];
			//Verificamos item por item até achar o ID da TAG que cadastrada
			$tag_list[] = array('label' => 'Selecione uma tag', 'value' => '');
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$tag_list[] = array('label' => $item['Title'], 'value' => $item['Id']);
			}	
		}
		else
		{
			$tag_list[] = array('label' => 'Erro...', 'value' => '');
		}

		return $tag_list;
	}



	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [ProductId] => [ProductName]
	//******************************************************************************
	//add_filter('get_leadlovers_product_list','get_leadlovers_product_options');
	// public function get_leadlovers_product_options($product_list)
	public function get_leadlovers_product_list()
	{
		// artigos que ajudam
		// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
		
		$url = 'http://llapi.leadlovers.com/webapi';
			
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		// cadastrado nas configurações do Woocommerce na aba Leadlovers
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );
		
		//envia a requisição de cadastro (/customer)
		$response = wp_remote_get( $url . '/products' . '?token=' . $token, array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
		));

		$status = wp_remote_retrieve_response_code($response);
		if ( 200 == $status ) 
		{
			// pegamos o "corpo" da resposta recebida...
			$response_body = wp_remote_retrieve_body( $response );
			// e transformamos de JSON em um array PHP normal.
			$response_data = json_decode( $response_body, true );

			// $form = $this->get_current_form();
			// //Pega o array com as configurações do Formulário atual
			// $settings = $this->get_form_settings( $form );
			//Pega os dados da Máquina/Sequência/Nível 

			$settings = $this->get_current_settings();

			$product_id = strval(rgar( $settings,'gf_leadlovers_product_choice'));
			if(!$product_id)
			{
				$product_list[] = array('label' => 'Selecione um Produto', 'value' => '1');
			}
			
			//$product_list = array();
			$items = $response_data['Items'];
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$product_list[] = array('label' => $item['ProductName'], 'value' => $item['ProductId']);
			}	
			
			return $product_list;
		}
		else
		{
			return array('label' => 'erro');
		}
	}

	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [value] => [label]
	//******************************************************************************
	public function get_leadlovers_dynamic_fields_list()
	{
		// artigos que ajudam
		// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
		
		$url = 'http://llapi.leadlovers.com/webapi';
			
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		// cadastrado nas configurações do Woocommerce na aba Leadlovers
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );
		
		//envia a requisição de cadastro (/customer)
		$response = wp_remote_get( $url . '/DynamicFields' . '?token=' . $token, array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
		));

		$status = wp_remote_retrieve_response_code($response);
		if ( 200 == $status ) 
		{
			// pegamos o "corpo" da resposta recebida...
			$response_body = wp_remote_retrieve_body( $response );
			// e transformamos de JSON em um array PHP normal.
			$response_data = json_decode( $response_body, true );
			
			$dynamic_field_list[] = array('label' => 'Selecione um Campo', 'value' => '');
			$items = $response_data['Items'];
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$dynamic_field_list[] = array('value' => $item['Id'], 'label' => $item['Name']);
			}	
			
			return $dynamic_field_list;
		}
		else
		{
			return array('label' => 'erro', 'value'=>'');
		}
	}
		
		
	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [value] => [label]
	//******************************************************************************
	public function get_leadlovers_machine_list()
	{
		// artigos que ajudam
		// https://wordpress.stackexchange.com/questions/404224/how-to-run-wp-remote-get-inside-of-a-loop-for-multiple-page-api-response
		
		$url = 'http://llapi.leadlovers.com/webapi';
			
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		// cadastrado nas configurações do Woocommerce na aba Leadlovers
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );
		
		//envia a requisição de cadastro (/customer)
		$response = wp_remote_get( $url . '/machines' . '?token=' . $token, array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
		));

		$status = wp_remote_retrieve_response_code($response);
		if ( 200 == $status ) 
		{
			// pegamos o "corpo" da resposta recebida...
			$response_body = wp_remote_retrieve_body( $response );
			// e transformamos de JSON em um array PHP normal.
			$response_data = json_decode( $response_body, true );
			
			// $form = $this->get_current_form();
			// //Pega o array com as configurações do Formulário atual
			// $settings = $this->get_form_settings( $form );
			//Pega os dados da Máquina/Sequência/Nível 

			$settings = $this->get_current_settings();

			$machine_id = strval(rgar( $settings,'gf_leadlovers_machine'));
			if(!$machine_id)
			{
				$machine_list[] = array('label' => 'Selecione uma Máquina', 'value' => '');
			}

			//$product_list = array();
			$items = $response_data['Items'];
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$machine_list[] = array('label' => $item['MachineName'].' ('.$item['MachineCode'].')', 'value' => $item['MachineCode']);
			}	
			
			return $machine_list;
		}
		else
		{
			return array('label' => 'erro', 'value'=>'');
		}
	}
		
	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [value] => [label]
	//******************************************************************************
	public function get_leadlovers_sequence_list($machine_id = '')
	{
		$form = $this->get_current_form();
		// //Pega o array com as configurações do Formulário atual
		$settings = $this->get_form_settings( $form );
		//Pega os dados da Máquina/Sequência/Nível 

		if(!$machine_id)
		{
			$machine_id = strval(rgar( $settings,'gf_leadlovers_machine'));

			if(!$machine_id)
			{
				return array(array('label' => 'Escolha uma Sequência', 'value' => ''));
			}
		}
		
		$machine_id = strval($machine_id); // passo o machine_id para inteiro

		$url = 'http://llapi.leadlovers.com/webapi';
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );

		//envia a requisição de cadastro (/customer)
		$response = wp_remote_get( $url . '/EmailSequences' . '?token=' . $token . '&machineCode=' . $machine_id, array(
			'method'      => 'GET',
			'timeout'     => 30,
			'redirection' => 10,
			'httpversion' => '1.0',
			'blocking'    => true,
			'headers'     => array('accept: application/json'),
		));

		$status = wp_remote_retrieve_response_code($response);
		if ( 200 == $status ) 
		{
			// pegamos o "corpo" da resposta recebida...
			$response_body = wp_remote_retrieve_body( $response );
			// e transformamos de JSON em um array PHP normal.
			$response_data = json_decode( $response_body, true );
			
			//$product_list = array();
			$items = $response_data['Items'];
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$machine_list[] = array('label' => $item['SequenceName'].' ('.$item['SequenceCode'].')', 'value' => $item['SequenceCode']);
			}	
			
			return $machine_list;
		}
		else
		{
			return array(array('label' => '#erro', 'value' => ''));
		}
	}

	//******************************************************************************
	//** FAZ A REQUISIÇÃO GET NA API E RETORNA OS DADOS
	//** NUM ARRAY ASSOCIATIVO [value] => [label]
	//******************************************************************************
	public function get_leadlovers_level_list($sequence_id = '', $machine_id = '')
	{

		$form = $this->get_current_form();
		// //Pega o array com as configurações do Formulário atual
		$settings = $this->get_form_settings( $form );
		//Pega os dados da Máquina/Sequência/Nível 

		if(!$sequence_id) 
		{
			// Passar por aqui significa que veio da classe e não do ajax
			// pois o ajax passa parametro, então: verifica se na classe já há
			// salvo algum valor

			$sequence_id = rgar( $settings,'gf_leadlovers_sequence');

			if(!$sequence_id)
			{
				// se nao tem nehm valor salvo retor um símbolo de bloqueio,
				// pois vai ter que esperar o usuario clicar primeiro na sequencia
				return array(array('label' => 'Escolha um Nível', 'value' => ''));
			}

			// então, uma vez que tem uma sequencia defeinida, 
			// significa que há uma máquina tb definida,
			// basta pega-la
			$machine_id = rgar( $settings,'gf_leadlovers_machine');
		}
		
		$sequence_id = strval($sequence_id); // passz para inteiro
		$machine_id = strval($machine_id); // passz para inteiro

		$url = 'http://llapi.leadlovers.com/webapi';
		// TOKEN PESSOAL CADASTRADO NA LEADLOVERS (https://app.leadlovers.com/settings)
		$token = $this->get_plugin_setting( 'gf_leadlovers_token' );

		//envia a requisição de cadastro (/customer)
		$response = wp_remote_get( $url . '/levels' .
			'?token=' . $token .
			'&machineCode=' . $machine_id . 
			'&sequenceCode=' . $sequence_id, 
			array(
				'method'      => 'GET',
				'timeout'     => 30,
				'redirection' => 10,
				'httpversion' => '1.0',
				'blocking'    => true,
				'headers'     => array('accept: application/json'),
		));

		$status = wp_remote_retrieve_response_code($response);
		if ( 200 == $status ) 
		{
			// pegamos o "corpo" da resposta recebida...
			$response_body = wp_remote_retrieve_body( $response );
			// e transformamos de JSON em um array PHP normal.
			$response_data = json_decode( $response_body, true );
			
			//$product_list = array();
			$items = $response_data['Items'];
			foreach ( $items as $item )
			{
				// salva a lista num array Id => Nome(Id) para melhor identificação
				$machine_list[] = array('label' => $item['Subject'].' ('.$item['Sequence'].')', 'value' => $item['Sequence']);
			}	

			return $machine_list;
		}
		else
		{
			return array(array('label' => '#erro', 'value' => ''));
		}
	}

	//******************************************************************************
	//** PEGA A LISTA DE OPÇÕES E DEVOLVE AO AJAX NA FORMA HTML
	//******************************************************************************
	public function get_leadlovers_sequence_list_ajax_callback()
	{
		$response = $this->get_leadlovers_sequence_list($_POST['machine_id']);
		$sequence_list_html = '<option value="">Escolha uma Sequência</option>';
		foreach ( $response as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$sequence_list_html .= '<option value="' . $item['value'] . '">' . $item['label'] . '</option>';
		}	

		wp_send_json($sequence_list_html);
	}

	//******************************************************************************
	//** PEGA A LISTA DE OPÇÕES E DEVOLVE AO AJAX NA FORMA HTML
	//******************************************************************************
	public function get_leadlovers_level_list_ajax_callback()
	{
		$response = $this->get_leadlovers_level_list($_POST['sequence_id'],$_POST['machine_id']);
		$level_list_html = '<option value="">Escolha um Nível</option>';
		foreach ( $response as $item )
		{
			// salva a lista num array Id => Nome(Id) para melhor identificação
			$level_list_html .= '<option value="' . $item['value'] . '">' . $item['label'] . '</option>';
		}	
		wp_send_json( $level_list_html );
	}

} // FIM DA CLASSE


		// DEBUG
		//$console = "<script>console.log(' Mensagem do além: " . $product_id . " - ". $product . "');</script>";
		//echo $console;
