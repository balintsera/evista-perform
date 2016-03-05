/******/ (function(modules) { // webpackBootstrap
/******/ 	// The module cache
/******/ 	var installedModules = {};

/******/ 	// The require function
/******/ 	function __webpack_require__(moduleId) {

/******/ 		// Check if module is in cache
/******/ 		if(installedModules[moduleId])
/******/ 			return installedModules[moduleId].exports;

/******/ 		// Create a new module (and put it into the cache)
/******/ 		var module = installedModules[moduleId] = {
/******/ 			exports: {},
/******/ 			id: moduleId,
/******/ 			loaded: false
/******/ 		};

/******/ 		// Execute the module function
/******/ 		modules[moduleId].call(module.exports, module, module.exports, __webpack_require__);

/******/ 		// Flag the module as loaded
/******/ 		module.loaded = true;

/******/ 		// Return the exports of the module
/******/ 		return module.exports;
/******/ 	}


/******/ 	// expose the modules object (__webpack_modules__)
/******/ 	__webpack_require__.m = modules;

/******/ 	// expose the module cache
/******/ 	__webpack_require__.c = installedModules;

/******/ 	// __webpack_public_path__
/******/ 	__webpack_require__.p = "";

/******/ 	// Load entry module and return exports
/******/ 	return __webpack_require__(0);
/******/ })
/************************************************************************/
/******/ ([
/* 0 */
/***/ function(module, exports) {

	'use strict';

	var _createClass = function () { function defineProperties(target, props) { for (var i = 0; i < props.length; i++) { var descriptor = props[i]; descriptor.enumerable = descriptor.enumerable || false; descriptor.configurable = true; if ("value" in descriptor) descriptor.writable = true; Object.defineProperty(target, descriptor.key, descriptor); } } return function (Constructor, protoProps, staticProps) { if (protoProps) defineProperties(Constructor.prototype, protoProps); if (staticProps) defineProperties(Constructor, staticProps); return Constructor; }; }();

	function _classCallCheck(instance, Constructor) { if (!(instance instanceof Constructor)) { throw new TypeError("Cannot call a class as a function"); } }

	var Perform = function () {
	  function Perform() {
	    _classCallCheck(this, Perform);
	  }

	  _createClass(Perform, [{
	    key: 'ajaxSuccess',
	    value: function ajaxSuccess(requestEvent) {
	      console.log(requestEvent);
	      var response = requestEvent.currentTarget.response;
	      /* console.log("AJAXSubmit - Success!"); */
	      console.log(response);
	      /* you can get the serialized data through the "submittedData" custom property: */
	      /* alert(JSON.stringify(this.submittedData)); */
	      var dumper = document.getElementById('dumper');
	      dumper.innerHTML = response.dump;
	    }
	  }, {
	    key: 'ajaxError',
	    value: function ajaxError(error) {
	      console.log('submit error', error);
	    }
	  }, {
	    key: 'submitData',
	    value: function submitData(oData) {
	      /* the AJAX request... */
	      var oAjaxReq = new XMLHttpRequest();
	      oAjaxReq.responseType = 'json';
	      oAjaxReq.submittedData = oData;
	      oAjaxReq.onload = this.ajaxSuccess;
	      oAjaxReq.onerror = this.ajaxError;
	      console.log('sending');

	      if (oData.technique === 0) {
	        /* method is GET */
	        oAjaxReq.open('get', oData.receiver.replace(/(?:\?.*)?$/, oData.segments.length > 0 ? '?' + oData.segments.join('&') : ''), true);
	        oAjaxReq.send(null);
	      } else {
	        /* method is POST */
	        oAjaxReq.open('post', oData.receiver, true);
	        console.log('post data', oData);
	        if (oData.technique === 3) {
	          /* enctype is multipart/form-data */
	          var sBoundary = '---------------------------' + Date.now().toString(16);
	          oAjaxReq.setRequestHeader('Content-Type', 'multipart\/form-data; boundary=' + sBoundary);
	          oAjaxReq.send('--' + sBoundary + '\r\n' + oData.segments.join('--' + sBoundary + '\r\n') + '--' + sBoundary + '--\r\n');
	        } else {
	          /* enctype is application/x-www-form-urlencoded or text/plain */
	          oAjaxReq.setRequestHeader('Content-Type', oData.contentType);
	          oAjaxReq.send(oData.segments.join(oData.technique === 2 ? '\r\n' : '&'));
	        }
	      }
	    }
	  }, {
	    key: 'processStatus',
	    value: function processStatus(oData) {
	      if (oData.status > 0) {
	        return;
	      }
	      /* the form is now totally serialized! do something before sending it to the server... */
	      /* doSomething(oData); */
	      console.log('AJAXSubmit - The form is now serialized. Submitting...');
	      this.submitData(oData);
	    }
	  }, {
	    key: 'plainEscape',
	    value: function plainEscape(sText) {
	      /* how should I treat a text/plain form encoding? what characters are not allowed? this is what I suppose...: */
	      /* "4\3\7 - Einstein said E=mc2" ----> "4\\3\\7\ -\ Einstein\ said\ E\=mc2" */
	      return sText.replace(/[\s\=\\]/g, '\\$&');
	    }
	  }, {
	    key: 'submitRequest',
	    value: function submitRequest(oTarget) {
	      var _this = this;

	      var sFieldType = false;
	      var oField = false;
	      var bIsPost = oTarget.method.toLowerCase() === 'post';
	      /* console.log("AJAXSubmit - Serializing form..."); */
	      var oData = {};
	      oData.contentType = bIsPost && oTarget.enctype ? oTarget.enctype : 'application\/x-www-form-urlencoded';
	      oData.technique = bIsPost ? oData.contentType === 'multipart\/form-data' ? 3 : oData.contentType === 'text\/plain' ? 2 : 1 : 0;
	      oData.receiver = oTarget.action;
	      oData.status = 0;
	      oData.segments = [];
	      var fFilter = oData.technique === 2 ? this.plainEscape : escape;
	      for (var nItem = 0; nItem < oTarget.elements.length; nItem++) {
	        oField = oTarget.elements[nItem];
	        if (!oField.hasAttribute('name')) {
	          continue;
	        }
	        sFieldType = oField.nodeName.toUpperCase() === 'INPUT' ? oField.getAttribute('type').toUpperCase() : 'TEXT';
	        if (sFieldType === 'FILE' && oField.files.length > 0) {
	          if (oData.technique === 3) {
	            var _loop = function _loop(nFile) {
	              var oFile = oField.files[nFile];
	              var oSegmReq = new FileReader();
	              var _self = _this;
	              /* (custom properties:) */
	              oSegmReq.segmentIdx = oData.segments.length;
	              oSegmReq.owner = oData;
	              /* (end of custom properties) */

	              oSegmReq.onload = function (oFREvt) {
	                this.owner.segments[this.segmentIdx] += oFREvt.target.result + '\r\n';
	                this.owner.status--;
	                _self.processStatus(this.owner);
	              };
	              oData.segments.push('Content-Disposition: form-data; name="' + oField.name + "\"; filename=\"" + oFile.name + '"\r\nContent-Type: ' + oFile.type + '\r\n\r\n');
	              oData.status++;
	              oSegmReq.readAsBinaryString(oFile);
	            };

	            /* enctype is multipart/form-data */
	            for (var nFile = 0; nFile < oField.files.length; nFile++) {
	              _loop(nFile);
	            }
	          } else {
	            /* enctype is application/x-www-form-urlencoded or text/plain or method is GET: files will not be sent! */
	            for (var nFile = 0; nFile < oField.files.length; oData.segments.push(fFilter(oField.name) + '=' + fFilter(oField.files[nFile++].name))) {}
	          }
	        } else if (sFieldType !== 'RADIO' && sFieldType !== 'CHECKBOX' || oField.checked) {
	          /* field type is not FILE or is FILE but is empty */
	          oData.segments.push(oData.technique === 3 ? /* enctype is multipart/form-data */
	          'Content-Disposition: form-data; name="' + oField.name + '"\r\n\r\n' + oField.value + '\r\n' : /* enctype is application/x-www-form-urlencoded or text/plain or method is GET */
	          fFilter(oField.name) + '=' + fFilter(oField.value));
	        }
	      }

	      // add form markup to perform parameter
	      if (oData.technique === 3) {
	        oData.segments.push('Content-Disposition: form-data; name="' + 'serform' + '"\r\n\r\n' + oTarget.outerHTML + '\r\n');
	      } else {
	        oData.segments.push('serform' + '=' + encodeURIComponent(oTarget.outerHTML));
	      }
	      this.processStatus(oData);
	    }
	  }, {
	    key: 'submit',
	    value: function submit(oFormElement, success, error) {
	      if (!oFormElement.action) {
	        throw new Error('No action defined for the submitted form');
	      }
	      if (success) {
	        this.ajaxSuccess = success;
	      }

	      if (error) {
	        this.ajaxError = error;
	      }
	      this.submitRequest(oFormElement);
	    }
	  }]);

	  return Perform;
	}();

	window.Perform = new Perform();

/***/ }
/******/ ]);