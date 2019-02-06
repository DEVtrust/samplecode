var express = require('express');
var router = express.Router();
var path = require('path');
var fs = require('fs');
var zip = new require('node-zip')();


/**
 * File Upload API
 * :container folder name in which file will be uploaded
 * return json 
 */
router.post('/:container/upload', function (req, res, next) {
    if (Object.keys(req.files).length == 0) {
        res.status(400).send('No files were uploaded.');
    }
    let sampleFile = req.files.file;
    const filename = Date.now() + '_' + sampleFile.name;
    sampleFile.mv('files/' + req.params.container + '/' + filename, function (err) {
        if (err)
            return res.status(500).send(err);

        res.send({'success': true, 'file_name': filename, 'message': 'File uploaded!'});
    });
});
/**
 * File download API
 * :container folder name in which file is located.
 * :filename filename for which download action performed. 
 * return json 
 */

router.get('/:container/download/:filename', function (req, res) {
    var filename = req.params.filename;
    var file = 'files/' + req.params.container + '/' + filename;
    var ext = filename.substring(filename.lastIndexOf('.')).toLowerCase();
    if (ext == '.pdf') {
        fs.readFile(file, function (err, data) {
            res.contentType("application/pdf");
            res.send(data);
        });
    } else {
            res.download(file);

    }
});
/**
 * File Delete API
 * :container folder name in which file exist
 * :filename file name which need to be deleted
 * return json
 */
router.delete('/:container/files/:filename', function (req, res) {
    var file = 'files/' + req.params.container + '/' + req.params.filename;
    fs.unlink(file, function () {
        res.send({
            status: "200",
            responseType: "string",
            response: "success"
        });
    });
});

module.exports = router;
