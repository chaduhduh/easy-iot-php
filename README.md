# Easy Iot
Perhaps the easiest IOT example using Flight (php), and Arduino (c). This is designed 
as a starting point for a prototype or other application. The demo provided is a real 
time temperature monitor.

# Required Materials
1. Arduino Uno R3
2. SparkFun Shield ESP8266 (WRL-13287)
3. Web Server for API

# Install/Run Steps
1. Clone repo
2. Enter your info:  AP ssid and key, server name into sketch (temperatureGauge.ino). ** optional update secret key in API and sketch. **
3. Load sketch onto board and power on
4. Point webserver to the projects public directory (This can be on a remote server or local instance,
any means of serving a PHP file)
5. Navigate to your url eg. [easyiot.chaddmyers.com](http://easyiot.chaddmyers.com/)
