<?php

$data = SnmpQuery::walk([
    'OPTICSIM-RADIO-TRS-PDH-MIB::opticsIMRadioPDHTTPBidTable',
])->valuesByIndex();

foreach ($data as $index => $entry) {
    if (isset($entry['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidCapacity'])) {
        //Discover Radio Capacities
        $oidCapacity = '.1.3.6.1.4.1.637.54.1.10.3.1.1.1.10.' . $index;
        $oidLicense =  '.1.3.6.1.4.1.637.54.1.10.3.1.1.1.31.' . $index;

        // Pull the Values
        $currentCapacity = $data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidCapacity'] ?? null;
        $currentLicense = $data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidMaxBandwidthForLicense'] ?? null;

        // Determine if the response is in Mbps or Kbps
        $isMbpsRegex = "/ Mb\/s/";
        $isMbps = preg_match($isMbpsRegex, $currentCapacity);

        // Set the $multiplier accordingly
        switch( $isMbps ) {
            case true:
                $multiplier = "1000000";
                break;

            case false:
                $multiplier = "1000";
                break;
        }

        // Cleanup the Numbers
        $currentCapacityNum = preg_replace($isMbpsRegex, "", $currentCapacity);
        $currentLicenseNum = preg_replace($isMbpsRegex, "", $currentLicense);

        if (is_numeric($currentCapacityNum)) {
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

            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'bitrate',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidCapacity,
                'sensor_index' => $index,
                'sensor_type' => 'linkCapacity',
                'sensor_descr' => $label . $port_descr . ' Link Capacity',
                'sensor_divisor' => 1,
                'sensor_multiplier' => $multiplier,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentCapacityNum * $multiplier,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
            ]));

            if($currentLicenseNum != 0) {
                app('sensor-discovery')->discover(new \App\Models\Sensor([
                    'poller_type' => 'snmp',
                    'sensor_class' => 'bitrate',
                    'device_id' => $device['device_id'],
                    'sensor_oid' => $oidLicense,
                    'sensor_index' => $index,
                    'sensor_type' => 'linkLicense',
                    'sensor_descr' => $label . $port_descr . ' Link License',
                    'sensor_divisor' => 1,
                    'sensor_multiplier' => $multiplier,
                    'sensor_limit' => null,
                    'sensor_limit_warn' => null,
                    'sensor_limit_low' => 40000001,
                    'sensor_limit_low_warn' => $currentCapacityNum * $multiplier,
                    'sensor_current' => $currentLicenseNum * $multiplier,
                    'entPhysicalIndex' => $index,
                    'entPhysicalIndex_measured' => 'ports',
                ]));
            }
        }
     }
}
