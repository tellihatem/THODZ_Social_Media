var base_url = '/';

const form = document.querySelector(".typing-area"),
inputField = form.querySelector(".input-field"),
incoming_id = form.querySelector(".incoming_id").value,
sendBtn = form.querySelector("button"),
chatBox = document.querySelector(".chat-box");


form.onsubmit = (e)=>{
    e.preventDefault();
}

inputField.focus();
inputField.onkeyup = ()=>{
    if(inputField.value != ""){
        sendBtn.classList.add("active");
    }else{
        sendBtn.classList.remove("active");
    }
}

sendBtn.onclick = ()=>{
    var form = "chat_form";
    $.ajax({
        type: "POST",
        contentType: false,
        cache: false,
        processData:false,
        url: base_url+"api/ajax.php?action=insertmessage",
        data: new FormData(document.getElementById(form)),
        success: function(data) {
           $("#input_chat").val('');
           scrollToBottom();  
        }
    });    
}
chatBox.onmouseenter = ()=>{
    chatBox.classList.add("active");
}

chatBox.onmouseleave = ()=>{
    chatBox.classList.remove("active");
}

setInterval(() =>{
    $.ajax({
      type: "POST",
      contentType: false,
      cache: false,
      processData:false,
      contentType: 'application/x-www-form-urlencoded',
      url: base_url+"api/process.php?action=chatmessages",
      data: $.param({ "chatwith": incoming_id}),
      success: function(data) {
          if ((!$.trim(data)) || data == null){   
              //the data is null or blank
          }else{
            chatBox.innerHTML = data;
            if(!chatBox.classList.contains("active")){
                scrollToBottom();
            }
          }
      },
  });
}, 500);

function scrollToBottom(){
    chatBox.scrollTop = chatBox.scrollHeight;
}