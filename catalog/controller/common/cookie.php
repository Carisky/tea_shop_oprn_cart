<?php
class ControllerCommonCookie extends Controller {
    public function index() {
        // скрываем ТОЛЬКО если уже согласились
        if (!empty($this->session->data['cookie_consent']) && $this->session->data['cookie_consent'] === true) {
            return '';
        }

        $this->load->language('common/cookie');
        $data['text_cookie']     = $this->language->get('text_cookie');
        $data['button_agree']    = $this->language->get('button_agree');
        $data['button_disagree'] = $this->language->get('button_disagree');
        $data['agree']    = $this->url->link('common/cookie/agree');
        $data['disagree'] = $this->url->link('common/cookie/disagree');
        return $this->load->view('common/cookie', $data);
    }

    public function agree() {
        $this->session->data['cookie_consent'] = true;
        $this->response->setOutput('1');
    }

    public function disagree() {
        // чтобы баннер снова показывался при «Отклонить»
        unset($this->session->data['cookie_consent']);
        $this->response->setOutput('1');
    }
}
?>
