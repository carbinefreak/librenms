<?php

$data = SnmpQuery::walk([
    'OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioAnalogueMeasuresTable',
    'OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel',
])->valuesByIndex();

foreach ($data as $index => $entry) {
    if (isset($entry['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresLocalTxPower'])) {
        //Discover Radio Power
        $oidLocalTxPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.2.' . $index; 
        $oidLocalRxPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.3.' . $index;
        $oidLocalRxDivPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.4.' . $index;
        $oidRemoteTxPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.6.' . $index; 
        $oidRemoteRxPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.7.' . $index;
        $oidRemoteRxDivPower = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.8.' . $index;
        $oidLocalMse = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.13.' . $index;
        $oidRemoteMse = '.1.3.6.1.4.1.637.54.1.10.1.1.4.1.14.' . $index;

        // Pull the Values
        $currentLocalTxPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresLocalTxPower'] ?? null;
        $currentLocalRxPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresLocalRxMainPower'] ?? null;
        $currentLocalRxDivPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresLocalRxDivPower'] ?? null;
        $currentRemoteTxPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresRemoteTxPower'] ?? null;
        $currentRemoteRxPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresRemoteRxMainPower'] ?? null;
        $currentRemoteRxDivPower = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresRemoteRxDivPower'] ?? null;
        $currentLocalMse = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresLocalMSE'] ?? null;
        $currentRemoteMse = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::analogueMeasuresRemoteMSE'] ?? null;


        // Pull User Label for Radio
        $label = $data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel'] ?? null;

        //Split the Index so we can reference different digits
        $indexArray = str_split($index);

        // Define descr as something sane as a fall through
        $port_descr = ' ' . $index;

        // Decode the ifIndex into human readable ifDescr
        // Make sure its a Radio ID that starts with a '5'
        if( $indexArray[0] == '5' ) {

            // For basic interfaces that the second digit is a '0'
            if( $indexArray[1] == '0' && ( count($indexArray) == 5 ) ) {
                $port_descr = ' Slot:' . $indexArray[2] . ' Port:' . $indexArray[4] . ' Ch:' . $indexArray[3];
            }

            // UBT-T interfaces where the second digit is '1'
            if( $indexArray[1] == '1' && ( count($indexArray) == 5 ) ) {

                // If the radio is a Primary when the 3rd digit is '1'
                if ( $indexArray[2] == '1' ) {
                    $port_descr = ' Primary Slot:' . $indexArray[3] . ' Port:' . $indexArray[4];
                }

                // If the radio is a Spare when the 3rd digit is '0'
                if ( $indexArray[2] == '0' ) {
                    $port_descr = ' Spare Slot:' . $indexArray[3] . ' Port:' . $indexArray[4];
                }
            }

            // For 6 digit IDs
            if( count($indexArray) == 6 ) {
                $port_descr = ' Slot:' . $indexArray[1] .  ' Port:' . $indexArray[2] . ' Ch:' . $indexArray[3];
            }

        }

        // Local TX Power
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidLocalTxPower,
            'sensor_index' => $index,
            'sensor_type' => 'localTxPower',
            'sensor_descr' => $label . $port_descr . ' Local Tx Power',
            'sensor_divisor' => 10,
            'sensor_limit' => '40',
            'sensor_limit_warn' => null,
            'sensor_limit_low' => '0',
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentLocalTxPower/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        // Local RX Power
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidLocalRxPower,
            'sensor_index' => $index,
            'sensor_type' => 'localRxPower',
            'sensor_descr' => $label . $port_descr . ' Local Rx Power',
            'sensor_divisor' => 10,
            'sensor_limit' => '-25',
            'sensor_limit_warn' => null,
            'sensor_limit_low' => '-60',
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentLocalRxPower/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        // If Local Diversiry RX is above -99dbm, discover it
        if( $currentLocalRxDivPower >= -990 ) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'signal',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidLocalRxDivPower,
                'sensor_index' => $index,
                'sensor_type' => 'localRxDivPower',
                'sensor_descr' => $label . $port_descr . ' Local Div Rx Power',
                'sensor_divisor' => 10,
                'sensor_multiplier' => 1,
                'sensor_limit' => '-25',
                'sensor_limit_warn' => null,
                'sensor_limit_low' => '-60',
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentLocalRxDivPower/10,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
            ]));

        }

        // Remote TX Power
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidRemoteTxPower,
            'sensor_index' => $index,
            'sensor_type' => 'remoteTxPower',
            'sensor_descr' => $label . $port_descr . ' Remote Tx Power',
            'sensor_divisor' => 10,
            'sensor_limit' => '40',
            'sensor_limit_warn' => null,
            'sensor_limit_low' => '0',
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentRemoteTxPower/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        // Remote RX Power
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidRemoteRxPower,
            'sensor_index' => $index,
            'sensor_type' => 'remoteRxPower',
            'sensor_descr' => $label . $port_descr . ' Remote Rx Power',
            'sensor_divisor' => 10,
            'sensor_limit' => '-25',
            'sensor_limit_warn' => null,
            'sensor_limit_low' => '-60',
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentRemoteRxPower/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        // If Remote Diversiry RX is above -99dbm, discover it
        if( $currentRemoteRxDivPower >= -990) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'signal',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidRemoteRxDivPower,
                'sensor_index' => $index,
                'sensor_type' => 'remoteRxDivPower',
                'sensor_descr' => $label . $port_descr . ' Remote Div Rx Power',
                'sensor_divisor' => 10,
                'sensor_limit' => '-25',
                'sensor_limit_warn' => null,
                'sensor_limit_low' => '-60',
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentRemoteRxDivPower/10,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
            ]));

        }

        // Local MSE
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidLocalMse,
            'sensor_index' => $index,
            'sensor_type' => 'localMse',
            'sensor_descr' => $label . $port_descr . ' Local MSE',
            'sensor_divisor' => 10,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentLocalMse/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        // Remote MSE
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'signal',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidRemoteMse,
            'sensor_index' => $index,
            'sensor_type' => 'RemoteMse',
            'sensor_descr' => $label . $port_descr . ' Remote MSE',
            'sensor_divisor' => 10,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentRemoteMse/10,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));        

    }
}

