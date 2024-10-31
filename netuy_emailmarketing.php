<?php
/**
 * Plugin Name: Netuy Email Marketing
 * Description: Agrega subscriptores al emailmarketing de Netuy a través de formularios generados por los plugins de formularios mas populares de Wordpress,  Contact form 7, WPForms y Elementor. 
 * Version: 1.2.0
 * Author: Netuy
 * Author URI: netuy.net
 */

# 10/2022 API ENDPOINT NETUY EMAILMARKETING
const ENDPOINT_API_EM = "https://clientes.emailmarketing.uy/api/v1/";

if ( !function_exists( 'is_plugin_active' ) ) {
    require_once( ABSPATH . '/wp-admin/includes/plugin.php' );
}

// Verificación de plugins en wordpress
if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
    add_action( 'wpcf7_mail_sent', 'netuy_em_wpcf7_process' );
}

// Verificación de plugins en wordpress
if ( is_plugin_active('wpforms-lite/wpforms.php') ||  is_plugin_active('wpforms/wpforms.php')) {
   add_action( 'wpforms_process', 'netuy_em_wpforms_process', 10, 3 );
}

// Verificación de plugin Elementor
if (is_plugin_active( 'elementor/elementor.php' )) {
    add_action( 'elementor_pro/forms/actions/register', 'add_new_subscription_form_action' );
}

add_action("init", "netuy_em_register_settings");
add_action('admin_menu', 'netuy_em_setting_page');


/**
 * Agregar nuevo subscriptor a Netuy Email Marketing.
 *
 * @since 1.0.0
 * @param ElementorPro\Modules\Forms\Registrars\Form_Actions_Registrar $form_actions_registrar
 * @return void
 */
function netuy_em_elementor_add_new_subscription_form_action( $form_actions_registrar ) {

	include_once( __DIR__ .  '/form-actions/netuy_emailmarketing__elementor_action.php' );

	$form_actions_registrar->register( new Netuy_Action_After_Submit() );

}

add_filter( 'plugin_action_links_' . plugin_basename(__FILE__), 'apd_settings_link' );
function apd_settings_link( array $links ) {
    $url = get_admin_url() . "options-general.php?page=netuy-em-plugin-setting-url";
    $settings_link = '<a href="' . $url . '">' . __('Ajustes', 'textdomain') . '</a>';
      $links[] = $settings_link;
    return $links;
}

// Option settings
function netuy_em_register_settings() {

    // Token Netuy Emailmarketing
    register_setting("netuy_em_options_group", "token");

    // List ID to insert subscriber
    register_setting("netuy_em_options_group", "list_uid");

    // Tag firstname to send in URL
    register_setting("netuy_em_options_group", "firstname");

    // Tag lastname to send in URL
    register_setting("netuy_em_options_group", "lastname");

    // Check if the plugin exists, if it exists, the id field of the wpforms form is added
    if ( is_plugin_active('wpforms-lite/wpforms.php') ) {
        register_setting("netuy_em_options_group", "wpforms_ids");
    }

    // Verificación de plugins en wordpress
    if ( is_plugin_active('contact-form-7/wp-contact-form-7.php') ) {
        register_setting("netuy_em_options_group", "wpcf7_ids");
    }


}

// Setting pagina de configuración plugin
function netuy_em_setting_page() {
    add_options_page('Netuy Email marketing', 'Netuy Email marketing Setting', 'manage_options', 'netuy-em-plugin-setting-url', 'netuy_em_plugin_html_form');
}


// HTML pagina configuración plugin
function netuy_em_plugin_html_form() {
        ?>
            <div class="wrap">
                <h2>Netuy Email marketing settings</h2>
                <form method="post" action="options.php">
                    <?php  esc_attr(settings_fields('netuy_em_options_group')); ?>

                <table class="form-table">

                    <tr>
                        <th><label for="token">Api token:</label></th>
                        <td>
                            <input type = 'text' class="regular-text" id="token" name="token" value="<?php  echo esc_attr(get_option('token')); ?>">
                        </td>
                    </tr>

                    <tr>
                        <th><label for="list_uid">Lista ID:</label></th>
                        <td>
                            <input type = 'text' class="regular-text" id="list_uid" name="list_uid" value="<?php echo esc_attr(get_option('list_uid')); ?>">
                            <h1 style="font-size:10px;"> Identificador ID de la lista a agregar, puede encontrarla en la URL de la lista. Ejemplo: https://emailmarketing.uy/lists/62daect08e8d0/overview. El UID de la lista es 62daect08e8d0'</h1>
                        </td>
                    </tr>
        
                    <tr>
                        <th><label for="firstname">Etiqueta Nombre:</label></th>
                        <td>
                            <input type = 'text' class="regular-text" id="firstname" name="firstname" value="<?php echo esc_attr(get_option('firstname')); ?>">
                            <h1 style="font-size:10px;"> En el gestor de campos de una lista, se encontra la etiqueta asignada a nombre, Ejemplo: SUBSCRIBER_NOMBRE ( Ingresar la etiqueta "NOMBRE" )</h1>
                        </td>
        
                    </tr>
        
                    <tr>
                        <th><label for="lastname">Etiqueta Apellido:</label></th>
                        <td>
                            <input type = 'text' class="regular-text" id="lastname" name="lastname" value="<?php echo esc_attr(get_option('lastname')); ?>">
                            <h1 style="font-size:10px;"> En el gestor de campos de una lista, se encontra las etiqueta asignada a apellido, Ejemplo: SUBSCRIBER_APELLIDO ( Ingresar la etiqueta "APELLIDO" )</h1>
                        </td>
                    </tr>
        
                    <tr>
                        <th><label for="list_uid">WPForms Ids:</label></th>
                    <td>
                        <input type = 'text' class="regular-text" id="wpforms_ids" name="wpforms_ids" value="<?php echo  esc_attr(get_option('wpforms_ids')); ?>">
                        <h1 style="font-size:10px;"> (ids de formularios separados por " , " Ejemplo: '15,513,3525' )</h1>
                    </td>
                    </tr>
                    
                    <tr>
                        <th><label for="list_uid">Contact Forms 7 Ids:</label></th>
                    <td>
                        <input type = 'text' class="regular-text" id="wpcf7_ids" name="wpcf7_ids" value="<?php echo  esc_attr(get_option('wpcf7_ids')); ?>">
                        <h1 style="font-size:10px;"> (ids de formularios separados por " , " Ejemplo: '15,513,3525' )</h1>
                    </td>
                    </tr>
                </table>
        
                <?php esc_attr(submit_button()); ?>
        
            </div>
        <?php
}



/**
 * Acción que se activa durante el procesamiento
 * de la entrada del formulario después de la validación del campo inicial.
 *
 * @link   https://wpforms.com/developers/wpforms_process/
 *
 * @param  array  $fields    Sanitized entry field. values/properties.
 * @param  array  $entry     Original $_POST global.
 * @param  array  $form_data Form data and settings.
 *
 */
function netuy_em_wpforms_process( $fields, $entry, $form_data ) {

    $ids = explode (",", get_option('wpforms_ids')); 

    if ( ! in_array($form_data[ 'id' ] , $ids) ) {
       return;
    }

    $email = null;
    $firstname = null;
    $lastname = null;
    foreach ($fields as $field) {
        if ($field["type"] == "email") {
            $email = $field["value"];
        }
        if ($field["type"] == "name") {
            if ($field["first"] == "") {
                $firstname = $field["value"];
            } else {
                $firstname = $field["first"];
                $lastname = $field["last"];
            }
        }
    }

    if ($email == null) {
        return;
    }

    $body = [
        'api_token' => get_option('token'),
        'list_uid' => get_option('list_uid'),
        'EMAIL' => $email
    ];

    if ($firstname != null ){
        $body[get_option('firstname')] =  $firstname;
    }

    if ($lastname != null ){
        $body[get_option('lastname')] =  $lastname;
    }

    netuy_em_send_subscriber($body);

}





/**
 * Acción que se activa durante el procesamiento
 * de la entrada del formulario después de la validación del campo inicial
 * de Contact Form 7.
 *
 *
 * @param json $contact_form  POST data.
 *
 */
function netuy_em_wpcf7_process( $contact_form ) {


    // encode array to json
    $submission = WPCF7_Submission::get_instance();

    if ( ! $submission ) {
        return;
    }

    $ids = explode (",", get_option('wpcf7_ids')); 

    if ( ! in_array($contact_form->id , $ids) ) {
       return;
    }

    $posted_data = $submission->get_posted_data();

    $firstname = $posted_data["your-name"];
    $lastname = $posted_data["your-lastname"];
    $email = $posted_data["your-email"];

    $body = [
        'api_token' => get_option('token'),
        'list_uid' => get_option('list_uid'),
        'EMAIL' => $email
    ];

    if ($firstname != null ){
        $body[get_option('firstname')] =  $firstname;
    }

    if ($lastname != null ){
        $body[get_option('lastname')] =  $lastname;
    }

    netuy_em_send_subscriber($body);

}

// POST curl envío de nuevo subscriptor
function netuy_em_send_subscriber($body) {

    $args = [
        'body' => $body
    ];

    wp_remote_post(ENDPOINT_API_EM."subscribers" , $args );

}
