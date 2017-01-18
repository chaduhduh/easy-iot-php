/************************************************************************
    Temperature Gauge Sketch.
  
    Built on arduino uno R3 with Spark Fun esp8266 sheild and library.
*************************************************************************/
#include <SoftwareSerial.h>
#include <SparkFunESP8266WiFi.h>


/*
    Access Point Info
  
    Info needed to connect to our network through access point
*/
const char mySSID[] = "";
const char myPSK[] = "";
ESP8266Client client;

/*
    Variables, Flags, Pins
*/
const int8_t gauge_pin = 0;
int use_reset = 0;          // turn this on to reset the esp on the sheild
int lost_access_point = 0;
int timeout = 30000;        // time in between pings to server
int errorTimeout = 5000;    // time to wait after failure
float temp = 0.00;          // Keep track of the last read temp
const char secret[] = "abf3fc0750a104077349";
const char host[] = "easyiot.chaddmyers.com";
const int64_t port = 80;
const char request[] = "POST /temp HTTP/1.1\n"
                       "Host: easyiot.chaddmyers.com\n"
                       "Accept: */*\n"
                       "Content-Type: application/x-www-form-urlencoded\n";



/*
    Setup

    This code will run prior to going into our main super loop. Load vars,
    make sure everything is present etc.
*/
void setup() {
    Serial.begin(9600);
    Serial.println("Booted");
    delay(500);
    // try to com with esp or fail

    int unit_status = esp8266.begin();
    if (unit_status != true) {
        Serial.println(F("Error talking to ESP8266."));
        errorLoop(unit_status);
    }
    Serial.println(F("ESP8266 Shield Present"));
    // if reset flag is 1 restart the device

    if(use_reset)       
      resetEsp(esp8266);
}


/*
    Main Logic
  
    Main logic for our device after setup is completed. We will perform a loop
    veryfying connection to access point, connecting to server, and making 
    a POST request with our temperature. We use a while loop here so we can use
    continue statements.
*/
void loop(){ while(1){

    if (esp8266.status() <= 0 && esp8266.getMode() != ESP8266_MODE_STA) {
    // if not a station, set to a station
        if (esp8266.setMode(ESP8266_MODE_STA) < 0){
            Serial.println(F("Error setting mode."));
            delay(errorTimeout);
            continue;
        }
    }
    Serial.println(F("Mode is station"));
    // connect to access point

    if (esp8266.status() <= 0) {
        Serial.print(F("Connecting to "));
        Serial.println(mySSID);
        if (esp8266.connect(mySSID, myPSK) < 0){    // -1 = TIMEOUT, -3 = FAIL       
            Serial.println(F("Error connecting"));
            delay(errorTimeout);
            continue;
        }
    }
    Serial.println("Connection Successful");
    // while connected to access point send data to server

    while(lost_access_point < 1){
        if (!client.available() && client.connect(host, port) <= 0){
            // if not currently connected and connect to client fails
            Serial.println(F("Failed to connect to server, retrying...\n"));
            delay(errorTimeout);
            if (esp8266.status() <= 0) {
                // if we didnt lose com with esp, reconnect to network
                Serial.println(F("Esp Failed, retrying....\n"));
                delay(errorTimeout);
                lost_access_point = 1;
            }
            continue;
        }
        if(client.available())
            Serial.println("Connected to server");

        if(temp == getTemp()){      // if same temp as before dont send request
            delay(errorTimeout);    // wait a bit and try again
            continue;
        }
        sendTempToServer(getTemp());
        delay(timeout);
    }
    restart_connection();   // something went wrong with access point
}}



/*
   Functions

   Function definitions for various tasks.
*/

void resetEsp(ESP8266Class esp8266){
    // resets esp on shield to default states 
    esp8266.reset();
    Serial.println("resetting");
    delay(3000);
}


float getTemp(){
    // Returns temperature in celsius
    float voltage;
    voltage = (analogRead(gauge_pin) * 0.004882814);    // converts the 0 to 1023 range into a 0.0 to 5.0 value that is the true voltage  
    temp = (voltage - 0.5) * 100.0;                      // formula comes from the temperature sensor datasheet
    return temp;                    
}


void errorLoop(int error){
  // never ending loop for complete failures
  Serial.print(F("Error: ")); Serial.println(error);
  Serial.println(F("Looping forever."));
  while (1){// loop forever
    }
}


void sendTempToServer(float temp){
    // prints our request to server, and mirrors that output to 
    // serial for easier use. This would likely be refactored if
    // you wanted to add more data to the request.
    client.print(request);
    client.print("Content-Length: ");
    int len = (strlen(secret)+7) + (sizeof(temp)+6);
    client.print(len);
    client.print("\n\n");
    client.print("secret=");
    client.print(secret);
    client.print("&temp=");
    client.print(temp);

    Serial.print("request:\n\n");
    Serial.print(request);  
    Serial.print("Content-Length: ");
    Serial.print(len);
    Serial.print("\n\n");
    Serial.print("secret=");
    Serial.print(secret);
    Serial.print("&temp=");
    Serial.print(temp);
    Serial.print("\n");
}

void restart_connection() {
    // wrapper to restart our ap connection, this is mainly for easier readability.
    Serial.println("lost internet");
    delay(timeout);
}
