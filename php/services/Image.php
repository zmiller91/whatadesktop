<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

/**
 * Description of Image
 *
 * @author zmiller
 */
class Image extends Service{
    
    protected function validate() {
        return true;
    }
    
    protected function authorize() {
        return true;
    }

    protected function execute() {
        $this->m_mData = "Hello World!";
    }

//put your code here
}
