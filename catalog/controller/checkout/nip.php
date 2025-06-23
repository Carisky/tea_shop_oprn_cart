<?php
class ControllerCheckoutNip extends Controller {
    public function index() {
    $this->load->language('api/nip');

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->response->addHeader('HTTP/1.1 405 Method Not Allowed');
        return;
    }

    $nip = isset($this->request->post['nip']) ? preg_replace('/\D/', '', $this->request->post['nip']) : '';

    if (!ctype_digit($nip)) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['error' => $this->language->get('error_nip_characters')]));
        return;
    }

    if (strlen($nip) !== 10) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['error' => $this->language->get('error_nip_length')]));
        return;
    }

    $curl = curl_init('https://wrapx.pl/getclientdatabynipex');
    curl_setopt_array($curl, [
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'scrf' => 'ewerffd12sdfsr45664ff',
            'nip' => $nip
        ]),
        CURLOPT_HTTPHEADER => ['Content-Type: application/x-www-form-urlencoded']
    ]);

    $response = curl_exec($curl);
    curl_close($curl);

    if (!$response) {
        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode(['error' => $this->language->get('error_nip_general')]));
        return;
    }

    $this->response->addHeader('Access-Control-Allow-Origin: *');
    $this->response->addHeader('Content-Type: application/json');
    $this->response->setOutput($response);
}

}
