<?php
/*
Plugin Name: Meu Plugin de Download de JSON
Description: Baixa um arquivo JSON de uma URL uma vez por dia ou manualmente e envia notificações por e-mail.
*/

// Função para fazer o download do JSON
function download_json($url, $destination_path) {
    $downloaded_json = file_get_contents($url);

    if ($downloaded_json) {
        file_put_contents($destination_path, $downloaded_json);
        return true;
    }
    return false;
}

// Função para enviar notificação por e-mail
function send_download_notification_email($success, $file_path) {
    $to = 'everton.gouveia@anjodaguarda.com'; // Substitua pelo seu endereço de e-mail
    $subject = 'Notificação de Download de JSON';
    $message = $success ? 'O JSON foi baixado com sucesso. <a href="'.$file_path.'">Link para o arquivo</a>' : 'O download do JSON falhou. Verifique a URL e as permissões.';
    wp_mail($to, $subject, $message);
}

// Ação agendada para executar o download uma vez por dia
function download_json_once_per_day() {
    $json_url = '/retornaConveniado/'; // Substitua pela URL do JSON que deseja baixar
    $upload_dir = wp_upload_dir();
    $json_file_path = $upload_dir['basedir'] . '/conveniados.json';
    $success = download_json($json_url, $json_file_path);
    send_download_notification_email($success, $json_file_path);
}
add_action('wp', 'schedule_json_download');
function schedule_json_download() {
    if (!wp_next_scheduled('download_json_once_per_day')) {
        wp_schedule_event(time(), 'daily', 'download_json_once_per_day');
    }
}

// Ação para download manual ao clicar em um botão
function download_json_manually() {
    $json_url = '/retornaConveniado/'; // Substitua pela URL do JSON que deseja baixar
    $upload_dir = wp_upload_dir();
    $json_file_path = $upload_dir['basedir'] . '/conveniados.json';
    $success = download_json($json_url, $json_file_path);
    send_download_notification_email($success, $json_file_path);
}
add_action('admin_menu', 'add_download_button');
function add_download_button() {
    add_menu_page('Download JSON', 'Download JSON', 'manage_options', 'download-json', 'download_json_manually');
}

// Função para exibir mensagem de sucesso
function download_json_success_notice() {
    echo '<div class="notice notice-success is-dismissible"><p>O JSON foi baixado com sucesso. <a href="'.$json_file_path.'">Link para o arquivo</a></p></div>';
}

// Função para exibir mensagem de erro
function download_json_error_notice() {
    echo '<div class="notice notice-error is-dismissible"><p>O download do JSON falhou. Verifique a URL e as permissões.</p></div>';
}

// Deixar o arquivo 'conveniados.json' na pasta sem data
function remove_date_from_json_filename($filename) {
    if ($filename === 'conveniados.json') {
        $filename = sanitize_file_name($filename);
    }
    return $filename;
}
add_filter('sanitize_file_name', 'remove_date_from_json_filename');

// Ativar a ação de download no ativação do plugin
register_activation_hook(__FILE__, 'schedule_json_download');

// Desativar a ação no desativação do plugin
register_deactivation_hook(__FILE__, 'deactivate_json_download');
function deactivate_json_download() {
    wp_clear_scheduled_hook('download_json_once_per_day');
}
