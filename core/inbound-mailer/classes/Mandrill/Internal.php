<?php

class Mandrill_Internal {
    public function __construct(Inbound_Mandrill $master) {
        $this->master = $master;
    }

}


