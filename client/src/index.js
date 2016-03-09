class Perform
{
  ajaxSuccess(requestEvent) {
    console.log(requestEvent);
    const response = requestEvent.currentTarget.response;
    /* console.log("AJAXSubmit - Success!"); */
    console.log(response);
    /* you can get the serialized data through the "submittedData" custom property: */
    /* alert(JSON.stringify(this.submittedData)); */
    const dumper = document.getelentById('dumper');
    dumper.innerHTML = response.dump;
  }

  ajaxError(error) {
    console.log('submit error', error);
  }

  submit(el) {
    console.log(el);
    const formMarkupInput = document.createElement('input');
    formMarkupInput.type = "hidden";
    formMarkupInput.name = "serform";
    formMarkupInput.value = encodeURIComponent(el.outerHTML);
    formMarkupInput.id = "serform";

    console.log(formMarkupInput);

    // check if its appended already and remove it
    const oldInput = document.getElementById('serform');
    if (oldInput !== null) {
      oldInput.parentNode.removeChild(oldInput);
    }

    // Add input with markup to the form
    el.appendChild(formMarkupInput)

    var formData = new FormData(el);
    console.log(formData);
    const xhr = new XMLHttpRequest();

    // Add any event handlers here...
    xhr.open('POST', '/multiple-file-uploads', true);

    xhr.addEventListener("load", this.ajaxSuccess);
    xhr.addEventListener("error", this.ajaxError);

    xhr.send(formData);
  }
}

window.Perform = new Perform();
