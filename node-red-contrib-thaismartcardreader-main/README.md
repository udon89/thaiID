# node-red-contrib-thaismartcardreader
A simple node for node-red to thaismartcardreader

## Installation
```
npm install https://github.com/monthop/node-red-contrib-thaismartcardreader
```

## Node-Red example
A sample node-red wiring to detect the thaismartcard:
```javascript
[{"id":"bb89c42af75abcf9","type":"inject","z":"8dd7ed531c4f6e42","name":"","props":[{"p":"payload"},{"p":"topic","vt":"str"}],"repeat":"","crontab":"","once":false,"onceDelay":0.1,"topic":"","payload":"","payloadType":"date","x":140,"y":80,"wires":[["c10f1bdd68fbe5bd"]]},{"id":"c10f1bdd68fbe5bd","type":"thaismartcardreader","z":"8dd7ed531c4f6e42","name":"","x":360,"y":80,"wires":[["b482970ea01186a5"]]},{"id":"b482970ea01186a5","type":"debug","z":"8dd7ed531c4f6e42","name":"debug 1","active":true,"tosidebar":true,"console":false,"tostatus":false,"complete":"true","targetType":"full","statusVal":"","statusType":"auto","x":600,"y":80,"wires":[]}]
```

## Credit
```
node-red-contrib-apdu2pcsc
@dogrocker/thaismartcardreader
```