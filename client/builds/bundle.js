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
	      var dumper = document.getelentById('dumper');
	      dumper.innerHTML = response.dump;
	    }
	  }, {
	    key: 'ajaxError',
	    value: function ajaxError(error) {
	      console.log('submit error', error);
	    }
	  }, {
	    key: 'submit',
	    value: function submit(el) {
	      console.log(el);
	      var formMarkupInput = document.createElement('input');
	      formMarkupInput.type = "hidden";
	      formMarkupInput.name = "serform";
	      formMarkupInput.value = encodeURIComponent(el.outerHTML);
	      formMarkupInput.id = "serform";

	      console.log(formMarkupInput);

	      // check if its appended already and remove it
	      var oldInput = document.getElementById('serform');
	      if (oldInput !== null) {
	        oldInput.parentNode.removeChild(oldInput);
	      }

	      // Add input with markup to the form
	      el.appendChild(formMarkupInput);

	      var formData = new FormData(el);
	      console.log(formData);
	      var xhr = new XMLHttpRequest();

	      // Add any event handlers here...
	      xhr.open('POST', '/multiple-file-uploads', true);

	      xhr.addEventListener("load", this.ajaxSuccess);
	      xhr.addEventListener("error", this.ajaxError);

	      xhr.send(formData);
	    }
	  }]);

	  return Perform;
	}();

	window.Perform = new Perform();

/***/ }
/******/ ]);