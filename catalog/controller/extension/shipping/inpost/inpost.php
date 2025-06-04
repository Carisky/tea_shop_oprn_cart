<?php
/**
 *  Avatec Inpost Integration
 *  Copyright (c) 2020 Grzegorz Miskiewicz
 *  All Rights Reserved
 */


class ControllerExtensionShippingInpostInpost extends Controller {

    public function index()
    {
        $this->load->model('extension/shipping/inpost');
        $data = $this->model_extension_shipping_inpost->getJson([
            'postcode' => (!empty( $this->request->get['postcode'] ) ? $this->request->get['postcode'] : null),
            'id' => (!empty( $this->request->get['id'] ) ? $this->request->get['id'] : null)
        ]);

        $this->response->addHeader('Content-Type: application/json');
        $this->response->setOutput(json_encode([
            'success' => true,
            'data' => $data
        ]));
    }
    public function getData()
    {

        $this->load->model('extension/shipping/inpost');
        $data = $this->model_extension_shipping_inpost->getJson([
            'postcode' => (!empty( $this->request->post['postcode'] ) ? $this->request->post['postcode'] : null),
            'id' => (!empty( $this->request->get['id'] ) ? $this->request->get['id'] : null)
        ]);

        if (!empty($this->request->post['html'])) {
            if (!empty($data)){
            // echo "<pre>"; print_r($data);
            ?><ul><?
            foreach ($data as $key => $value) {?>
            <li><a href="javascript:;" onclick="btnSelectPoint('<?=$value["id"]?>', '<?=$value["street"]?>, <?=$value["city"]?>');"><?=$value["id"]?>-<?=$value["street"]?>-<?=$value["description"]?></a></li>  
            <?}
            ?></ul><?
            } else {
                $this->response->setOutput("Not found!");
            }
        } else {
            $this->response->addHeader('Content-Type: application/json');
            $this->response->setOutput(json_encode([
                'success' => true,
                'data' => $data
            ]));    
        }
    }

    public function setData()
    {
		$this->load->language('extension/shipping/inpost');
        $this->load->model('extension/shipping/inpost');
		if(empty($this->request->post['id'])) exit('no id');
				
        $point = $this->model_extension_shipping_inpost->getPoint($this->request->post['id']);
		if(empty($point)) exit();
		
		$quote = $this->model_extension_shipping_inpost->buildQuote($point);
		
		$this->session->data['shipping_methods']['inpost']['quote']['point'] = $quote;
		$this->session->data['shipping_method'] = $quote;

        exit('1');
    }
}
