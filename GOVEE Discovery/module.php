<?php

declare(strict_types=1);
	class GOVEEDiscovery extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			$this->ForceParent('{87579ED9-E5BC-EBCD-0095-8D532ECC16BC}');

			$this->RegisterPropertyBoolean('Active', false);
			$this->RegisterAttributeString('Devices', '{}');

			$this->RegisterTimer("ScanTimer", 0, 'GVL_ScanDevices(' . $this->InstanceID . ');');
		}

		public function Destroy()
		{
			//Never delete this line!
			parent::Destroy();
		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();

			if ($this->ReadPropertyBoolean('Active')) 
			{
                $this->ScanDevices();
				$this->SetTimerInterval('ScanTimer', 60 * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('ScanTimer', 0);
                $this->SetStatus(104);
            }

			
			$filter = '.*scan.*';
			$this->SetReceiveDataFilter($filter);
		}

		public function SendData(string $Payload)
		{
			//IPS_LogMessage('Discovery Send', $Payload);
			
			if ($this->HasActiveParent()) 
			{
				$this->SendDataToParent(json_encode([
				
					'DataID' => '{244A8DDD-ECFF-489F-6B91-F436AFAE7115}',
					'Buffer' => $Payload,
					'ClientIP'=> '239.255.255.250',
					'ClientPort'=> 4001,
					'Broadcast' => true,
					'EnableBroadcast' => true,
				]));
			}
		}


		public function ReceiveData($JSONString)
        {
        	//IPS_LogMessage('Discovery RECV', $JSONString);
			
			$data = json_decode($JSONString, true);
            $devices = json_decode($this->ReadAttributeString('Devices'), true);

            $buffer = json_decode($data['Buffer'], true);
            $data = $buffer['msg']['data'];

            //IPS_LogMessage('test', print_r($devices, true));

            if (array_key_exists('device', $data)) 
			{
                if (!array_key_exists($data['device'], $devices)) 
				{
                    $devices[$data['device']] = [
                        'ip'              => $data['ip'],
                        'sku'             => $data['sku'],
                        'bleVersionHard'  => $data['bleVersionHard'],
                        'bleVersionSoft'  => $data['bleVersionSoft'],
                        'wifiVersionHard' => $data['wifiVersionHard'],
                        'wifiVersionSoft' => $data['wifiVersionSoft']
                    ];
                }
            }
            $this->WriteAttributeString('Devices', json_encode($devices));
			IPS_LogMessage('Discovery', print_r($devices, true));

		}



		public function ScanDevices()
        {
			$this->WriteAttributeString('Devices', '{}');
			//IPS_LogMessage('Descvery Send', "Scan Start");
			$govee_message = '{"msg":{"cmd":"scan","data":{"account_topic":"reserve"}}} ';
			$this->SendData($govee_message);

		}

		public function GetDevices()
        {
			
			return ($this->ReadAttributeString('Devices'));
		}




	}