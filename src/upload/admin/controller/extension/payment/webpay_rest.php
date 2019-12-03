<?php

require_once(DIR_CATALOG.'controller/extension/payment/libwebpay_rest/HealthCheck.php');
require_once(DIR_CATALOG.'controller/extension/payment/libwebpay_rest/LogHandler.php');

class ControllerExtensionPaymentWebpayRest extends Controller {

    private $error = array();

    private $default_config = array(
        'test_mode' => "INTEGRACION",
        'commerce_code' => "597055555532",
        'api_key' => "579B532A7440BB0C9079DED94D31EA1615BACEB56610332264630D42D0A36B1C"
    );

    private $sections = array('commerce_code', 'api_key', 'test_mode');

    private function loadResources() {
        $this->load->language('extension/payment/webpay_rest');
        $this->load->model('setting/setting'); //load model in: $this->model_setting_setting
        $this->load->model('localisation/order_status'); //load model in: $this->model_localisation_order_status
    }

    public function index() {

        session_start();

        $_SESSION["DIR_SYSTEM"] = DIR_SYSTEM;
        $_SESSION["DIR_IMAGE"] = DIR_IMAGE;

        $this->loadResources();

        $this->document->setTitle($this->language->get('heading_title'));;

        
        $redirs = array('authorize', 'finish', 'error', 'reject');
        foreach ($redirs as $value) {
            $this->request->post['payment_webpay_rest_url_'.$value] = HTTP_CATALOG . 'index.php?route=extension/payment/webpay_rest/' .$value;
        }

        // validacion de modificaciones

        if (($this->request->server['REQUEST_METHOD'] == 'POST') && $this->validate()) {
            $this->model_setting_setting->editSetting('payment_webpay_rest', $this->request->post);

            $this->session->data['success'] = $this->language->get('text_success');

            $this->response->redirect($this->url->link('extension/payment/webpay_rest', 'user_token=' .$this->session->data['user_token'] . '&type=payment', true));
        }

        // se imprimen errores si existen

        if (isset($this->error['warning'])) {
            $data['error_warning'] = $this->error['warning'];
        } else {
            $data['error_warning'] = '';
        }

        foreach ($this->sections as $value) {
            if (isset($this->error['payment_webpay_rest_'.$value])) {
                $data['error_'.$value] = $this->error['payment_webpay_rest_'.$value];
            } else {
                $data['error_'.$value] = '';
            }
        }

        $vars = array(
            'entry_commerce_code',
            'entry_api_key',
            'entry_test_mode',
            'entry_total',
            'entry_geo_zone',
            'entry_status',
            'entry_sort_order',
            'entry_completed_order_status',
            'entry_rejected_order_status',
            'tab_settings',
            'entry_canceled_order_status'
        );

        foreach ($vars as $var) {
            $data[$var] = $this->language->get($var);
        }

        // se declaran los breadcrumbs (el menu de seguimiento)

        $data['breadcrumbs'] = array();

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_home'),
            'href' => $this->url->link('common/dashboard', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('text_webpay_rest'),
            'href' => $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true),
        );

        $data['breadcrumbs'][] = array(
            'text' => $this->language->get('heading_title'),
            'href' => $this->url->link('extension/payment/webpay_rest', 'user_token=' . $this->session->data['user_token'], true),
        );

        $data['action'] = $this->url->link('extension/payment/webpay_rest', 'user_token=' . $this->session->data['user_token'], true);

        $data['cancel'] = $this->url->link('marketplace/extension', 'user_token=' . $this->session->data['user_token'] . '&type=payment', true);

        foreach ($this->sections as $value) {
            if (isset($this->request->post['payment_webpay_rest_'.$value])) {
                $data['payment_webpay_rest_'.$value] = $this->request->post['payment_webpay_rest_'.$value];
            } else if ($this->config->get('payment_webpay_rest_'.$value)) {
                $data['payment_webpay_rest_'.$value] = $this->config->get('payment_webpay_rest_'.$value);
            } else {
                $data['payment_webpay_rest_'.$value] = $this->default_config[$value];
            }
        }

        $selects = array('total', 'completed_order_status', 'rejected_order_status', 'canceled_order_status', 'geo_zone', 'sort_order', 'status');

        foreach ($selects as $value) {
            if (isset($this->request->post['payment_webpay_rest_'.$value])) {
                $data['payment_webpay_rest_'.$value] = $this->request->post['payment_webpay_rest_'.$value];
            } else {
                $data['payment_webpay_rest_'.$value] = $this->config->get('payment_webpay_rest_'.$value);
            }
        }

        $this->load->model('localisation/order_status');
        $data['order_statuses'] = $this->model_localisation_order_status->getOrderStatuses();
        $this->load->model('localisation/geo_zone');
        $data['geo_zones'] = $this->model_localisation_geo_zone->getGeoZones();

        // si desde la instalacion inicial no toma los parametros por defecto

        $args = array(
            'MODO' => $this->default_config['test_mode'],
            'COMMERCE_CODE' => $this->default_config['commerce_code'],
            'API_KEY' => $this->default_config['api_key'],
            'ECOMMERCE' => 'opencart'
        );

        if (isset($this->request->post['payment_webpay_rest_commerce_code'])) {
            $args = array(
                'MODO' => $this->request->post['payment_webpay_rest_test_mode'],
                'COMMERCE_CODE' => $this->request->post['payment_webpay_rest_commerce_code'],
                'API_KEY' => $this->request->post['payment_webpay_rest_api_key'],
                'ECOMMERCE' => 'opencart'
            );
        } else if ($this->config->get('payment_webpay_rest_commerce_code')) {
            $args = array(
                'MODO' => $this->config->get('payment_webpay_rest_test_mode'),
                'COMMERCE_CODE' => $this->config->get('payment_webpay_rest_commerce_code'),
                'API_KEY' => $this->config->get('payment_webpay_rest_api_key'),
                'ECOMMERCE' => 'opencart'
            );
        }

        $_SESSION["config"] = $args;

        $hc = new HealthCheck($args);
        $healthcheck = json_decode($hc->printFullResume(), true);

        $lh = new LogHandler();
        $loghandler = json_decode($lh->getResume(), true);

        $data['hc_data'] = $hc->printFullResume();
        $data['healthcheck'] = $healthcheck;
        $data['lg_data'] = $lh->getResume();
        $data['loghandler'] = $loghandler;

        if (isset($loghandler['last_log']['log_content'])) {
            $data['res_logcontent'] = json_encode($loghandler['last_log']['log_content']);
            $data['log_file'] = $loghandler['last_log']['log_file'];
            $data['log_file_weight'] = $loghandler['last_log']['log_weight'];
            $data['log_file_regs'] = $loghandler['last_log']['log_regs_lines'];
        } else {
            $data['res_logcontent'] = $loghandler['last_log'][0];
            $data['log_file'] = json_encode($data['res_logcontent']);
            $data['log_file_weight'] = $data['log_file'];
            $data['log_file_regs'] = $data['log_file'];
        }

        if ($loghandler['config']['status'] === false) {
            $data['estado_logs'] = "<span class='label label-warning'>Desactivado sistema de Registros</span>";
        } else {
            $data['estado_logs'] = "<span class='label label-success'>Activado sistema de Registros</span>";
        }

        $data['log_list'] = $loghandler['logs_list'];
        $data['log_dir'] = stripslashes(json_encode($loghandler['log_dir']));
        $data['log_count'] = json_encode($loghandler['logs_count']['log_count']);
        $data['tb_max_logs_days'] = $loghandler['config']['max_logs_days'];

        $data['tb_max_logs_weight'] = $loghandler['config']['max_log_weight'];

        $data['url_create_pdf_report'] = '../catalog/controller/extension/payment/libwebpay_rest/CreatePdf.php?document=report';
        $data['url_create_pdf_php_info'] = '../catalog/controller/extension/payment/libwebpay_rest/CreatePdf.php?document=php_info';
        $data['url_check_conn'] = '../catalog/controller/extension/payment/libwebpay_rest/CheckConn.php';

        $data['header'] = $this->load->controller('common/header');
        $data['column_left'] = $this->load->controller('common/column_left');
        $data['footer'] = $this->load->controller('common/footer');

        $this->response->setOutput($this->load->view('extension/payment/webpay_rest', $data));
    }

    private function validate() {

        if (!$this->user->hasPermission('modify', 'extension/payment/webpay_rest')) {
            $this->error['warning'] = $this->language->get('error_permission');
        }

        foreach ($this->sections as $value) {
            if (!$this->request->post['payment_webpay_rest_'.$value]) {
                $this->error[$value] = $this->language->get('error_'.$value);
            }
        }

        return !$this->error;
    }
}
