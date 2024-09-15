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
			
			$data = json_decode($JSONString, true); // neune Geräte
			$buffer = json_decode($data['Buffer'], true);
            $new_device = $buffer['msg']['data'];

            $devices = json_decode($this->ReadAttributeString('Devices'), true); // lese vorhandene Geräte

            if (array_key_exists('device', $new_device)) 
			{
				$devices += [$new_device['device'] => $new_device];

				$this->WriteAttributeString('Devices', json_encode($devices));
			}

		}



		public function ScanDevices()
        {
			$this->WriteAttributeString('Devices', '{}');
			//IPS_LogMessage('Descvery Send', "Scan Start");
			$govee_message = '{"msg":{"cmd":"scan","data":{"account_topic":"reserve"}}} ';
			$this->SendData($govee_message);

		}

		public function GetNewDevices()
        {
			return ($this->ReadAttributeString('Devices'));
		}
	}