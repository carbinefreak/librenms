<?php

$data = SnmpQuery::walk([
    'OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioFrequencyTable',
    'OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel',
])->valuesByIndex();

foreach ($data as $index => $entry) {
    if (isset($entry['OPTICSIM-RADIO-TRS-COMMON-MIB::radioRxFrequency'])) {
        //Discover Radio Frequencies
        $oidLocalTx = '.1.3.6.1.4.1.637.54.1.10.1.1.1.1.1.' . $index; 
        $oidLocalRx = '.1.3.6.1.4.1.637.54.1.10.1.1.1.1.2.' . $index;

        // Pull the Values
        $currentLocalTx = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::radioTxFrequency'] ?? null;
        $currentLocalRx = $data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::radioRxFrequency'] ?? null;
        $currentLocalMinTx = $data[$index]['OPTICSIMOPTICSIM-RADIO-TRS-COMMON-MIB::radioMinTxFrequency'] ?? null;
        $currentLocalMaxTx = $data[$index]['OPTICSIMOPTICSIM-RADIO-TRS-COMMON-MIB::radioMaxTxFrequency'] ?? null;
        $currentLocalMinRx = $data[$index]['OPTICSIMOPTICSIM-RADIO-TRS-COMMON-MIB::radioMinRxFrequency'] ?? null;
        $currentLocalMaxRx = $data[$index]['OPTICSIMOPTICSIM-RADIO-TRS-COMMON-MIB::radioMaxRxFrequency'] ?? null;

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
                if ( $indexArray[2] == '1' ) {
                    $port_descr = ' Spare Slot:' . $indexArray[3] . ' Port:' . $indexArray[4];
                }
            }

            // For 6 digit IDs
            if( count($indexArray) == 6 ) {
                $port_descr = ' Slot:' . $indexArray[1] .  ' Port:' . $indexArray[2] . ' Ch:' . $indexArray[3];
            }

        }

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'frequency',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidLocalTx,
            'sensor_index' => $index,
            'sensor_type' => 'txFrequency',
            'sensor_descr' => $label . $port_descr . ' Tx Frequency',
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1000,
            'sensor_limit' => $currentLocalMaxTx,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => $currentLocalMinTx,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentLocalTx*1000,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'frequency',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidLocalRx,
            'sensor_index' => $index,
            'sensor_type' => 'rxFrequency',
            'sensor_descr' => $label . $port_descr . ' Rx Frequency',
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1000,
            'sensor_limit' => $currentLocalMaxRx,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => $currentLocalMinRx,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentLocalRx*1000,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
        ]));
    }
}

