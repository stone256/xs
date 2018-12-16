<?php


class sitemin_model_mailsender {

    function send($mrr){

        return email::send($mrr);

    }

}
