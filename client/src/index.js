class AJAXSubmit
{

  ajaxSuccess() {
    /* console.log("AJAXSubmit - Success!"); */
    console.log(this.response);
    /* you can get the serialized data through the "submittedData" custom property: */
    /* alert(JSON.stringify(this.submittedData)); */
    const dumper = document.getElementById('dumper');
    dumper.innerHTML = this.response.dump;
  }

  submitData(oData) {
    /* the AJAX request... */
    const oAjaxReq = new XMLHttpRequest();
    oAjaxReq.responseType = 'json';
    oAjaxReq.submittedData = oData;
    oAjaxReq.onload = this.ajaxSuccess;
    oAjaxReq.onerror = function(error) {
      console.log('error', error);
    };
    console.log('sending');

    if (oData.technique === 0) {
      /* method is GET */
      oAjaxReq.open('get', oData.receiver.replace(/(?:\?.*)?$/, oData.segments.length > 0 ? '?' + oData.segments.join('&') : ''), true);
      oAjaxReq.send(null);
    } else {
      /* method is POST */
      oAjaxReq.open('post', oData.receiver, true);
      console.log(oData.segments);
      if (oData.technique === 3) {
        /* enctype is multipart/form-data */
        const sBoundary = '---------------------------' + Date.now().toString(16);
        oAjaxReq.setRequestHeader('Content-Type', 'multipart\/form-data; boundary=' + sBoundary);
        oAjaxReq.send('--' + sBoundary + '\r\n' + oData.segments.join('--' + sBoundary + '\r\n') + '--' + sBoundary + '--\r\n');
      } else {
        /* enctype is application/x-www-form-urlencoded or text/plain */
        oAjaxReq.setRequestHeader('Content-Type', oData.contentType);
        oAjaxReq.send(oData.segments.join(oData.technique === 2 ? '\r\n' : '&'));
      }
    }
  }

  processStatus(oData) {
    if (oData.status > 0) { return; }
    /* the form is now totally serialized! do something before sending it to the server... */
    /* doSomething(oData); */
    console.log('AJAXSubmit - The form is now serialized. Submitting...');
    this.submitData(oData);
  }

  pushSegment(oFREvt) {
    this.owner.segments[this.segmentIdx] += oFREvt.target.result + '\r\n';
    this.owner.status--;
    this.processStatus(this.owner);
  }

  plainEscape(sText) {
    /* how should I treat a text/plain form encoding? what characters are not allowed? this is what I suppose...: */
    /* "4\3\7 - Einstein said E=mc2" ----> "4\\3\\7\ -\ Einstein\ said\ E\=mc2" */
    return sText.replace(/[\s\=\\]/g, '\\$&');
  }

  submitRequest(oTarget) {
    let sFieldType = false;
    let oField = false;
    const bIsPost = oTarget.method.toLowerCase() === 'post';
    /* console.log("AJAXSubmit - Serializing form..."); */
    const oData = {};
    oData.contentType = bIsPost && oTarget.enctype ? oTarget.enctype : 'application\/x-www-form-urlencoded';
    oData.technique = bIsPost ? oData.contentType === 'multipart\/form-data' ? 3 : oData.contentType === 'text\/plain' ? 2 : 1 : 0;
    console.log(oData.technique);
    oData.receiver = oTarget.action;
    oData.status = 0;
    oData.segments = [];
    const fFilter = oData.technique === 2 ? this.plainEscape : escape;
    for (let nItem = 0; nItem < oTarget.elements.length; nItem++) {
      oField = oTarget.elements[nItem];
      if (!oField.hasAttribute('name')) { continue; }
      sFieldType = oField.nodeName.toUpperCase() === 'INPUT' ? oField.getAttribute('type').toUpperCase() : 'TEXT';
      if (sFieldType === 'FILE' && oField.files.length > 0) {
        if (oData.technique === 3) {
          /* enctype is multipart/form-data */
          for (let nFile = 0; nFile < oField.files.length; nFile++) {
            const oFile = oField.files[nFile];
            const oSegmReq = new FileReader();
            /* (custom properties:) */
            oSegmReq.segmentIdx = oData.segments.length;
            oSegmReq.owner = oData;
            /* (end of custom properties) */
            oSegmReq.onload = this.pushSegment;
            oData.segments.push('Content-Disposition: form-data; name="' + oField.name + "\"; filename=\"" + oFile.name + '"\r\nContent-Type: ' + oFile.type + '\r\n\r\n');
            oData.status++;
            oSegmReq.readAsBinaryString(oFile);
          }
        } else {
          /* enctype is application/x-www-form-urlencoded or text/plain or method is GET: files will not be sent! */
          for (let nFile = 0; nFile < oField.files.length; oData.segments.push(fFilter(oField.name) + '=' + fFilter(oField.files[nFile++].name)));
        }
      } else if ((sFieldType !== 'RADIO' && sFieldType !== 'CHECKBOX') || oField.checked) {
        /* field type is not FILE or is FILE but is empty */
        oData.segments.push(
          this.technique === 3 ? /* enctype is multipart/form-data */
            'Content-Disposition: form-data; name="' + oField.name + '"\r\n\r\n' + oField.value + '\r\n'
          : /* enctype is application/x-www-form-urlencoded or text/plain or method is GET */
            fFilter(oField.name) + '=' + fFilter(oField.value)
        );
      }
    }

    // add form markup to perform parameter
    if (this.technique === 3) {
      oData.segments.push('Content-Disposition: form-data; name="' + 'serform' + '"\r\n\r\n' + oTarget.outerHTML + '\r\n');
    } else {
      oData.segments.push('serform' + '=' + encodeURIComponent(oTarget.outerHTML));
    }
    this.processStatus(oData);
  }

  submit(oFormElement) {
    if (!oFormElement.action) {
      throw new Error('No action defined for the submitted form');
    }

    this.submitRequest(oFormElement);
  }
}

window.AJAXSubmit = new AJAXSubmit();
