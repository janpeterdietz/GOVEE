<?php

declare(strict_types=1);
	class GOVEELight extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			//$this->RequireParent('{87579ED9-E5BC-EBCD-0095-8D532ECC16BC}');
			$this->ConnectParent('{87579ED9-E5BC-EBCD-0095-8D532ECC16BC}');

			if (!IPS_VariableProfileExists('GVL.ColorTemperature')) 
			{
				IPS_CreateVariableProfile('GVL.ColorTemperature', VARIABLETYPE_INTEGER);
				IPS_SetVariableProfileText('GVL.ColorTemperature', '', ' K');
				IPS_SetVariableProfileValues ('GVL.ColorTemperature', 0, 4300, 1);
			}

			$this->RegisterPropertyBoolean('Active', false);
			$this->RegisterPropertyInteger('Interval', 10);

			$this->RegisterPropertyString("DeviceID", "");
			$this->RegisterPropertyString("IPAddress", "192.168.178.1");
			

			$this->RegisterVariableBoolean ("State", $this->Translate("State"),  "~Switch", 10) ;
			$this->RegisterVariableInteger('Brightness', $this->Translate('Brightness'), '~Intensity.100', 20);
			$this->RegisterVariableInteger('Color', $this->Translate('Color'), '~HexColor', 30);
			$this->RegisterVariableInteger('ColorTemperature', $this->Translate('Color Temperature'), 'GVL.ColorTemperature', 40);
            
			$this->EnableAction('State');
			$this->EnableAction('Brightness');
			$this->EnableAction('Color');
			$this->EnableAction('ColorTemperature');		
       
			
			$this->RegisterPropertyInteger("UpdateInterval", 10);

			$this->RegisterTimer("Updatestate", ($this->ReadPropertyInteger("Interval"))*1000, 'GVL_UpdateState(' . $this->InstanceID . ');');
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
                $this->SetTimerInterval('Updatestate', $this->ReadPropertyInteger('Interval') * 1000);
                $this->SetStatus(102);
            } else {
                $this->SetTimerInterval('Updatestate', 0);
                $this->SetStatus(104);
            }


			$IPAddress=$this->ReadPropertyString("IPAddress");
			$this->SetSummary($IPAddress);

			
			$filter = '.*cmd.*';
			$filter .= '.*devStatus.*';

			$filter .= '.*"ClientIP":.*';
			$filter .= '.*' . '"' . $IPAddress. '"'. '.*';
		

			$this->SetReceiveDataFilter($filter);

		}

		public function Send()
		{
			$this->SendDataToParent(json_encode(['DataID' => '{244A8DDD-ECFF-489F-6B91-F436AFAE7115}']));
		}

		public function SendData(string $Payload)
		{
			if ($this->HasActiveParent()) 
			{
				$this->SendDataToParent(json_encode([
					'DataID' => '{244A8DDD-ECFF-489F-6B91-F436AFAE7115}',
					'Buffer' => $Payload,
					'ClientIP' => $this->ReadPropertyString("IPAddress"),	
            		'ClientPort' => 4003,
					'Broadcast' => false,
					'EnableBroadcast' => false
				]));
			}
		}


		public function ReceiveData($JSONString)
        {
			//IPS_LogMessage('Device RECV', $JSONString);
			$data = json_decode($JSONString);
			

			//IPS_LogMessage('Device RECV', $data->Buffer . ' - ' . $data->ClientIP . ' - ' . $data->ClientPort);
			
			if ($data->ClientIP == $this->ReadPropertyString("IPAddress"))
			{
				$buffer = json_decode($data->Buffer, true);

				if ($buffer['msg']['cmd'] == 'devStatus')
				{
					$deviceData = $buffer['msg']['data'];

					$this->SetValue('State', $deviceData['onOff']);
					$this->SetValue('Brightness', $deviceData['brightness']);

					$r =  $deviceData['color']['r'];
					$g =  $deviceData['color']['g'];
					$b =  $deviceData['color']['b'];

					$color = (int) ( ($r * 256 * 256) + ($g * 256) + $b);

					$this->SetValue('Color', $color);
					$this->SetValue('ColorTemperature', $deviceData['colorTemInKelvin']);
				}
			}
		}
		
		public function RequestAction($Ident, $Value)
        {
            switch ($Ident) {
                case 'State':
					$this->setState($Value);
					break;
                case 'Brightness':
                    $this->setBrightness($Value);
                    break;
                case 'Color':
                    $this->setColor($Value);
                    break;
                case 'ColorTemperature':
					if (!is_int($Value))
					{
						$Value = intval($Value);
					}
                    $this->setColorTemperature($Value);
                    break;
                default:
                    $this->SendDebug(__FUNCTION__, 'Invalid Action: ' . $Ident, 0);
                    break;
            }
        }


        public function UpdateState()
        {
			$this->SetTimerInterval("Updatestate", 0);
	
			$govee_message = '{"msg":{"cmd":"devStatus","data":{}}} ';
			$this->SendData($govee_message);

			$this->SetTimerInterval('Updatestate', $this->ReadPropertyInteger('Interval') * 1000);
		}



        private function setState(bool $state)
        {
			$value = (int)$state;
			$govee_message = '{ "msg" :{ "cmd" : "turn", "data" : { "value":' . $value . ' }}} ';
			
			$this->SendData($govee_message);
			$this->SetTimerInterval("Updatestate", 800);
		}

        private function setBrightness(int $brightness)
        {
			if ($brightness > 0) 
			{
				if ($brightness < 100)
				{
					$value = $brightness;
				}
				else
				{
					$value = 100;
				}
			}
			else
			{
				$value = 0;
			}
			
			$govee_message = '{ "msg" :{ "cmd" : "brightness", "data" : { "value":' . $value . ' }}} ';
			$this->SendData($govee_message);
    		$this->SetTimerInterval("Updatestate", 800);
	    }

        private function setColor(int $color)
        {
			$r = (int) ( ($color / 256 / 256) );
			$g = (int) ( ($color - ($r*256*256)) / 256 );
			$b = (int) ( ($color - ($r*256*256) - $g*256) ) ;
			
			$govee_message = '{ "msg" :{
										   "cmd":"colorwc", 
										   "data":  { 
												"color": { 
												  "r": '.  $r . ', 
												  "g": ' . $g . ', 
												  "b": ' . $b . '
												  }
												  , 
												  "colorTemInKelvin": ' . 0 . '
												}
											}
										}' ;
			
			$this->SendData($govee_message);
    		$this->SetTimerInterval("Updatestate", 800);
	    }

        private function setColorTemperature(int $colortemperature)
        {
			$govee_message = '{ "msg" :{
											"cmd":"colorwc", 
											"data":  { 
					 							"color": { 
					   							"r": ' . 0 . ', 
					   							"g": ' . 0 . ', 
					   							"b": ' . 0 . '
					   							}
					   							, 
					   							"colorTemInKelvin": ' . $colortemperature . '
					 							}
				 							}
			 							}';

			 $this->SendData($govee_message);
			 $this->SetTimerInterval("Updatestate", 800);
        }
	}