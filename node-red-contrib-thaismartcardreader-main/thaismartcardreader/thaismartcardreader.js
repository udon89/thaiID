module.exports = function(RED) {
    function ThaiSmartCardReaderNode(config) {

        const fs = require('fs')
        const { Reader } = require('@dogrocker/thaismartcardreader')
        const path = require('path')

        const myReader = new Reader()

        RED.nodes.createNode(this,config);
        var node = this;

        this.on('input', function(msg) {
            msg.payload = "Hello world";
            node.send(msg);
        });

        process.on('unhandledRejection', (reason) => {
            console.log('From Global Rejection -> Reason: ' + reason);
          });
          
          console.log('Waiting For Device !')
          myReader.on('device-activated', async (event) => {
            console.log('Device-Activated')
            console.log(event.name)
            console.log('=============================================')
          })
          
          myReader.on('error', async (err) => {
            console.log(err)
          })
          
          myReader.on('image-reading', (percent) => {
            console.log(percent)
          })
          
          myReader.on('card-inserted', async (person) => {
            const cid = await person.getCid()
            const thName = await person.getNameTH()
            const enName = await person.getNameEN()
            const dob = await person.getDoB()
            const issueDate = await person.getIssueDate()
            const expireDate = await person.getExpireDate()
            const address = await person.getAddress()
            const issuer = await person.getIssuer()
          
            console.log(`CitizenID: ${cid}`)
            console.log(`THName: ${thName.prefix} ${thName.firstname} ${thName.lastname}`)
            console.log(`ENName: ${enName.prefix} ${enName.firstname} ${enName.lastname}`)
            console.log(`DOB: ${dob.day}/${dob.month}/${dob.year}`)
            console.log(`Address: ${address}`)
            console.log(`IssueDate: ${issueDate.day}/${issueDate.month}/${issueDate.year}`)
            console.log(`Issuer: ${issuer}`)
            console.log(`ExpireDate: ${expireDate.day}/${expireDate.month}/${expireDate.year}`)

            var msg = {};
            msg.payload = {
                cid : cid,
                thName : thName,
                enName : enName,
                dob : dob,
                issueDate : issueDate,
                expireDate : expireDate,
                address : address,
                issuer : issuer
            }
            node.send(msg);            
          
            // console.log('=============================================')
            // console.log('Receiving Image')
            // const photo = await person.getPhoto()
            // console.log(`Image Saved to ${path.resolve('')}/${cid}.bmp`)
            // console.log('=============================================')
            // const fileStream = fs.createWriteStream(`${cid}.bmp`)
            // const photoBuff = Buffer.from(photo)
            // fileStream.write(photoBuff)
            // fileStream.close()
          })
          
          myReader.on('device-deactivated', () => {
            console.log('device-deactivated')
          })        
    }
    RED.nodes.registerType("thaismartcardreader",ThaiSmartCardReaderNode);
}