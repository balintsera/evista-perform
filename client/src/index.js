class Perform
{
  ajaxSuccess(result) {
    //console.log('ajax success result', result);
    const response = JSON.parse(result.target.response);
    //console.log('response', response);

    let dumper = document.getElementById('dumper');
    dumper.innerHTML = response.dump;
  }

  ajaxError(error) {
    console.log('submit error', error);
  }

  submit(el, success_cb, error_cb) {
    if (success_cb) {
      this.ajaxSuccess = success_cb;
    }

    if (error_cb) {
      this.ajaxError = error_cb;
    }

    // check if its appended already and remove it
    const oldInput = document.getElementById('serform');
    if (oldInput !== null) {
      oldInput.parentNode.removeChild(oldInput);
    }

    const formMarkupInput = document.createElement('input');
    formMarkupInput.type = "hidden";
    formMarkupInput.name = "serform";
    formMarkupInput.value = encodeURIComponent(el.outerHTML);
    formMarkupInput.id = "serform";

    // Add input with markup to the form
    el.appendChild(formMarkupInput)

    var formData = new FormData(el);

    const xhr = new XMLHttpRequest();
    const url = el.action;
    if (url === null) {
      throw new Error('No url set in the form action attribute');
    }
    // Add any event handlers here...
    xhr.open('POST', url, true);

    xhr.addEventListener("load", this.ajaxSuccess);
    xhr.addEventListener("error", this.ajaxError);

    xhr.send(formData);
  }
}

window.Perform = new Perform();
