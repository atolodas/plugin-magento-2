<?php
/**
 * Copyright 2015 Compropago.
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */
/**
 * Compropago $Library
 * @author Eduardo Aguilar <eduardo.aguilar@compropago.com>
 */

namespace ComproPago\MgPayment\Block\Webhook;

use Magento\Framework\View\Element\Template;
use Magento\Payment\Block\Form;

use ComproPago\MgPayment\Model\Api\CompropagoSdk\Client;
use ComproPago\MgPayment\Model\Api\CompropagoSdk\Factory\Factory;
use ComproPago\MgPayment\Model\Api\CompropagoSdk\Tools\Validations;

class Webhook extends Template
{
    public function procesWebhook($json = null)
    {

        /**
         * Se valida el request y se transforma con la cadena a un objeto de tipo CpOrderInfo con el Factory
         */
        if(empty($json) || !$resp_webhook = Factory::cpOrderInfo($json)){
            die('Tipo de Request no Valido');
        }


        /**
         * Gurdamos la informacion necesaria para el Cliente
         * las llaves de compropago y el modo de ejecucion de la tienda
         */
        $publickey     = "pk_live_xxxxxxxxxxxxxxxxxxx";
        $privatekey    = "sk_live_xxxxxxxxxxxxxxxxxxx";
        $live          = true; // si es modo pruebas cambiar por 'false'


        /**
         * Se valida que las llaves no esten vacias (No es obligatorio pero si recomendado)
         */
        //keys set?
        if (empty($publickey) || empty($privatekey)){
            die("Se requieren las llaves de compropago");
        }





        try{
            /**
             * Se incializa el cliente
             */
            $client = new Client(
                $publickey,
                $privatekey,
                $live
            );

            /**
             * Validamos que nuestro cliente pueda procesar informacion
             */
            Validations::validateGateway($client);
        }catch (\Throwable $e) {
            //something went wrong at sdk lvl
            die($e->getMessage());
        }


        /**
         * Verificamos si recivimos una peticion de prueba
         */
        if($resp_webhook->getId()=="ch_00000-000-0000-000000"){
            die("Probando el WebHook?, <b>Ruta correcta.</b>");
        }



        try{
            /**
             * Verificamos la informacion del Webhook recivido
             */
            $response = $client->api->verifyOrder($resp_webhook->getId());


            /**
             * Comprovamos que la verificacion fue exitosa
             */
            if($response->getType() == 'error'){
                die('Error procesando el número de orden');
            }


            /**
             * Generamos las rutinas correspondientes para cada uno de los casos posible del webhook
             */
            switch ($response->getType()){
                case 'charge.success':
                    // TODO: Actions on success payment
                    break;
                case 'charge.pending':
                    // TODO: Actions on pending payment
                    break;
                case 'charge.declined':
                    // TODO: Actions on declined payment
                    break;
                case 'charge.expired':
                    // TODO: Actions on expired payment
                    break;
                case 'charge.deleted':
                    // TODO: Actions on deleted payment
                    break;
                case 'charge.canceled':
                    // TODO: Actions on canceled payment
                    break;
                default:
                    die('Invalid Response type');
            }

        }catch (Exception $e){
            //something went wrong at sdk lvl
            die($e->getMessage());
        }

    }
}