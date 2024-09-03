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
			$this->RegisterAttributeString('NewDevicesConfiguration', '{}');


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
				$this->SetTimerInterval('ScanTimer', 30 * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('ScanTimer', 0);
                $this->SetStatus(104);
            }

			
			$filter = '.*scan.*';

			//$filter = '.*"ClientIP":.*';
			//$filter .= '.*' . '"' . $IPAddress. '"'. '.*';
		

			$this->SetReceiveDataFilter($filter);
		}

		public function SendData(string $Payload)
		{
			IPS_LogMessage('Discovery Send', $Payload);
			
			if ($this->HasActiveParent()) 
			{
				IPS_LogMessage('Discovery Send', 'has active parent');
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
			
            $new_device_config = json_decode($this->ReadAttributeString('NewDevicesConfiguration'), true); // Platz füer Gereäte Configurationen / Info
			
            $data = json_decode($JSONString, true);
		
			$new_device = json_decode($data['Buffer'], true)['msg']['data'];        
		
			$new_device_config ['deviceconf'] = $new_device;

            $this->WriteAttributeString('NewDevicesConfiguration', json_encode($new_device_config));
			
		}



		public function ScanDevices()
        {
			
			//IPS_LogMessage('Descvery Send', "Scan Start");
			$govee_message = '{"msg":{"cmd":"scan","data":{"account_topic":"reserve"}}} ';
			$this->SendData($govee_message);

		}




	}