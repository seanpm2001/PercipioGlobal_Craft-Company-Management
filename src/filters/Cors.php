<?php

namespace percipiolondon\companymanagement\filters;

class Cors extends \yii\filters\Cors
{
    public function prepareHeaders($requestHeaders) {
        $responseHeaders = parent::prepareHeaders($requestHeaders);
        if (isset($this->cors['Access-Control-Allow-Headers'])) {
            $responseHeaders['Access-Control-Allow-Headers'] = implode(', ', $this->cors['Access-Control-Allow-Headers']);
        }
        return $responseHeaders;
    }
}
