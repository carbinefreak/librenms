<?php

$radio_data = SnmpQuery::walk([
    'OPTICSIM-RADIO-TRS-PDH-MIB::opticsIMRadioPDHTTPBidTable',
    'OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioTxMuteTable',
])->valuesByIndex();

// Walk the tables for the radio states
foreach ($radio_data as $index => $entry) {
    if (isset($entry['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidModulation'])) {
        //Discover Radio States
        $oidMod = '.1.3.6.1.4.1.637.54.1.10.3.1.1.1.2.' . $index; 
        $oidState = '.1.3.6.1.4.1.637.54.1.10.3.1.1.1.5.' . $index;
        $oidManLocalMute = '.1.3.6.1.4.1.637.54.1.10.1.1.7.1.1.' . $index;
        $oidAutoLocalMute = '.1.3.6.1.4.1.637.54.1.10.1.1.7.1.2.' . $index;
        $oidManRemoteMute = '.1.3.6.1.4.1.637.54.1.10.1.1.7.1.3.' . $index;
        $oidAutoRemoteMute = '.1.3.6.1.4.1.637.54.1.10.1.1.7.1.4.' . $index;


        // Pull the Values
        $currentMod = $radio_data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidModulation'] ?? null;
        $currentState = $radio_data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidOperationalState'] ?? null;
        $currentManLocalMute = $radio_data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioManLocalTxMute'] ?? null;
        $currentAutoLocalMute = $radio_data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioAutoLocalTxMute'] ?? null;
        $currentManRemoteMute = $radio_data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioManRemoteTxMute'] ?? null;
        $currentAutoRemoteMute = $radio_data[$index]['OPTICSIM-RADIO-TRS-COMMON-MIB::opticsIMRadioAutoRemoteTxMute'] ?? null;

        // Pull User Label for Radio
        $label = $radio_data[$index]['OPTICSIM-RADIO-TRS-PDH-MIB::radioPDHTTPBidUserLabel'] ?? null;

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

        //Create State Indexes
        $mod_state_name = 'bidModulation';
        create_state_index(
            $mod_state_name,
            [
                ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => '4QAM'],
                ['value' => 2, 'generic' => 1, 'graph' => 0, 'descr' => '8QAM'],
                ['value' => 3, 'generic' => 0, 'graph' => 0, 'descr' => '16QAM'],
                ['value' => 4, 'generic' => 0, 'graph' => 0, 'descr' => '32QAM'],
                ['value' => 5, 'generic' => 0, 'graph' => 0, 'descr' => '32TCM'],
                ['value' => 6, 'generic' => 0, 'graph' => 0, 'descr' => '64QAM'],
                ['value' => 7, 'generic' => 0, 'graph' => 0, 'descr' => '128QAM'],
                ['value' => 8, 'generic' => 0, 'graph' => 0, 'descr' => '128TCM'],
                ['value' => 9, 'generic' => 0, 'graph' => 0, 'descr' => '256QAM'],
                ['value' => 10, 'generic' => 0, 'graph' => 0, 'descr' => '512QAM'],
                ['value' => 11, 'generic' => 0, 'graph' => 0, 'descr' => '1024QAM'],
                ['value' => 12, 'generic' => 0, 'graph' => 0, 'descr' => '2048QAM'],
                ['value' => 13, 'generic' => 0, 'graph' => 0, 'descr' => '4096QAM'],
            ]
        );

        $opstate_state_name = 'opState';
        create_state_index(
            $opstate_state_name,
            [
                ['value' => 0, 'generic' => 2, 'graph' => 0, 'descr' => 'Offline'],
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Online'],
            ]
        );

        $manlocalmute_state_name = 'ManLocalMute';
        create_state_index(
            $manlocalmute_state_name,
            [
                ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Muted'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Unmuted'],
            ]
        );

        $autolocalmute_state_name = 'AutoLocalMute';
        create_state_index(
            $autolocalmute_state_name,
            [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Muted'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Unmuted'],
            ]
        );

        $manremotemute_state_name = 'ManRemoteMute';
        create_state_index(
            $manremotemute_state_name,
            [
                ['value' => 1, 'generic' => 1, 'graph' => 0, 'descr' => 'Muted'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Unmuted'],
            ]
        );

        $autoremotemute_state_name = 'AutoRemoteMute';
        create_state_index(
            $autoremotemute_state_name,
            [
                ['value' => 1, 'generic' => 0, 'graph' => 0, 'descr' => 'Muted'],
                ['value' => 2, 'generic' => 0, 'graph' => 0, 'descr' => 'Unmuted'],
            ]
        );

        // Define the State Sensors
        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidMod,
            'sensor_index' => $index,
            'sensor_type' => $mod_state_name,
            'sensor_descr' => $label . $port_descr . ' Modulation',
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentMod,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'Modulation',
        ]));

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidState,
            'sensor_index' => $index,
            'sensor_type' => $opstate_state_name,
            'sensor_descr' => $label . $port_descr . ' State',
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $currentState,
            'entPhysicalIndex' => $index,
            'entPhysicalIndex_measured' => 'ports',
            'group' => 'Status',
        ]));
        if (isset($currentManLocalMute)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidManLocalMute,
                'sensor_index' => $index,
                'sensor_type' => $manlocalmute_state_name,
                'sensor_descr' => $label . $port_descr . ' Local Man Mute',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentManLocalMute,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'group' => 'Mute',
            ]));
        }
        if (isset($currentAutoLocalMute)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidAutoLocalMute,
                'sensor_index' => $index,
                'sensor_type' => $autolocalmute_state_name,
                'sensor_descr' => $label . $port_descr . ' Local Auto Mute',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentAutoLocalMute,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'group' => 'Mute',
            ]));
        }
        if (isset($currentManRemoteMute)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidManRemoteMute,
                'sensor_index' => $index,
                'sensor_type' => $manremotemute_state_name,
                'sensor_descr' => $label . $port_descr . ' Remote Man Mute',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentManRemoteMute,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'group' => 'Mute',
            ]));
        }
        if (isset($currentAutoRemoteMute)) {
            app('sensor-discovery')->discover(new \App\Models\Sensor([
                'poller_type' => 'snmp',
                'sensor_class' => 'state',
                'device_id' => $device['device_id'],
                'sensor_oid' => $oidAutoRemoteMute,
                'sensor_index' => $index,
                'sensor_type' => $autoremotemute_state_name,
                'sensor_descr' => $label . $port_descr . ' Remote Auto Mute',
                'sensor_divisor' => 1,
                'sensor_multiplier' => 1,
                'sensor_limit' => null,
                'sensor_limit_warn' => null,
                'sensor_limit_low' => null,
                'sensor_limit_low_warn' => null,
                'sensor_current' => $currentAutoRemoteMute,
                'entPhysicalIndex' => $index,
                'entPhysicalIndex_measured' => 'ports',
                'group' => 'Mute',
            ]));
        }
    }
}


// Fetch Alarm Data
$alarm_data = SnmpQuery::mibs(['all'],
    )->walk([
    'TSDIM-SUPPORT-MIB::tsdimAPTTable',
])->valuesByIndex();

// Walk the tables for the radio states
foreach ($alarm_data as $index => $entry) {
    if (isset($entry['TSDIM-SUPPORT-MIB::tsdimAPTAlarmSeverity'])) {
        // Figure out the chassis alarm code

        //Explode the Index to extract the base index
        $baseIndex = explode('.', $index);

        $oidAlarmSev = '.1.3.6.1.4.1.637.54.1.1.3.1.2.1.6.' . $baseIndex[0] . '.' . $baseIndex[2];
        $oidProbableCause =  '.1.3.6.1.4.1.637.54.1.1.3.1.2.1.1.' . $baseIndex[0] . '.' . $baseIndex[2];

        // Pull the Values
        $AlarmSev = $alarm_data[$index]['TSDIM-SUPPORT-MIB::tsdimAPTAlarmSeverity'] ?? null;
        $probableCauseReponseRaw = $alarm_data[$index]['TSDIM-SUPPORT-MIB::tsdimAPTAlarmProbableCause'] ?? null;

        //Explode the Reposone to extract the alarm text
        $probableCauseReponse = explode(':', $probableCauseReponseRaw);

        $alarmFilter = [ "/opticsIM/i", "/alarm/i", "/raise/i", "/clear/i" ];

        // Remove the leading "opticsIMAlarm"
        $probableCauseReponse = preg_replace($alarmFilter, "", $probableCauseReponse[2]);


        $alarmsev_state_name = 'AlarmSeverity';
        create_state_index(
            $alarmsev_state_name,
            [
                ['value' => 0, 'generic' => 0, 'graph' => 0, 'descr' => 'Clear'],
                ['value' => 1, 'generic' => 2, 'graph' => 0, 'descr' => 'Critical'],
                ['value' => 2, 'generic' => 2, 'graph' => 0, 'descr' => 'Major'],
                ['value' => 3, 'generic' => 1, 'graph' => 0, 'descr' => 'Minor'],
                ['value' => 4, 'generic' => 1, 'graph' => 0, 'descr' => 'Warning'],
                ['value' => 5, 'generic' => 3, 'graph' => 0, 'descr' => 'Indeterminate'],
            ]
        );

        app('sensor-discovery')->discover(new \App\Models\Sensor([
            'poller_type' => 'snmp',
            'sensor_class' => 'state',
            'device_id' => $device['device_id'],
            'sensor_oid' => $oidAlarmSev,
            'sensor_index' => $index,
            'sensor_type' => $alarmsev_state_name,
            'sensor_descr' => 'Alarm ID:' . $baseIndex[2] . ' ' . $probableCauseReponse,
            'sensor_divisor' => 1,
            'sensor_multiplier' => 1,
            'sensor_limit' => null,
            'sensor_limit_warn' => null,
            'sensor_limit_low' => null,
            'sensor_limit_low_warn' => null,
            'sensor_current' => $AlarmSev,
            'entPhysicalIndex' => null,
            'entPhysicalIndex_measured' => null,
            'group' => 'Alarms',
        ]));
    }
}
