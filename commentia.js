var api = "commentia.api.php";
var pageid = document.getElementsByTagName("html")[0].getAttribute("data-pageid");

function httpRequest() {
  try {
    http_request = new XMLHttpRequest();
    return http_request;
  } catch (e) {
    try {
      http_request = new ActiveXObject("Msxml2.XMLHTTP");
      return http_request;
    } catch (e) {
      try {
        http_request = new ActiveXObject("Microsoft.XMLHTTP");
        return http_request;
      } catch (e) {
        alert("Your browser broke!");
        return false;
      }
    }
  }
}

function refreshComments() {
  var comments_section = document.getElementById("comments_pageid-" + pageid);
  var url = api + "?pageid=" + pageid + "&action=display";

  var http_request = httpRequest();

  console.log("GET request to: " + url);

  http_request.onreadystatechange = function() {

    if (http_request.readyState == 4) {
      comments_section.innerHTML = http_request.responseText;
      http_request = null;
    }
  }

  http_request.open("GET", url, true);
  http_request.send();
}

function showReplyArea(caller) {
  var comment = findCommentRoot(caller);
  var ucid = comment.getAttribute('data-ucid');

  var reply_area_id = 'reply-area-' + ucid;
  var reply_box_id = 'reply-box-' + ucid;
  var reply_button_id = 'reply-button-' + ucid;
  var cancel_button_id = 'edit-cancel-button-' + ucid;

  if (!document.getElementById(reply_area_id)) {
    var reply_area = document.createElement('div');
    reply_area.setAttribute('id', reply_area_id);

    var reply_box = document.createElement('textarea');
    reply_box.setAttribute('id', reply_box_id);
    reply_box.setAttribute('onkeyup', "autoGrow(this);");
    reply_area.appendChild(reply_box);

    var reply_button = document.createElement('button');
    reply_button.innerHTML = 'reply';
    reply_button.setAttribute('id', reply_button_id);
    reply_button.setAttribute('onclick', 'postReply(this);');
    reply_area.appendChild(reply_button);

    var cancel_button = document.createElement('button');
    cancel_button.innerHTML = 'cancel';
    cancel_button.setAttribute('id', cancel_button_id);
    cancel_button.setAttribute('onclick', 'hideReplyArea(this);');
    reply_area.appendChild(cancel_button);
    comment.getElementsByClassName('commentia-reply_area')[0].appendChild(reply_area);
  }
  document.getElementById(reply_area_id).style.display = "block";
}

function hideReplyArea(caller) {
  var comment = findCommentRoot(caller);
  var ucid = comment.getAttribute('data-ucid');
  var reply_area_id = 'reply-area-' + ucid;

  document.getElementById(reply_area_id).style.display = "none";
}

function findCommentRoot(el) {
  while ((el = el.parentNode) && !el.hasAttribute('data-ucid'));
  return el;
}

function postReply(caller) {
  var comment = findCommentRoot(caller);
  var ucid = comment.getAttribute('data-ucid');
  var reply_path = comment.getAttribute('data-reply-path');
  var reply_box_id = 'reply-box-' + ucid;

  var comments_section = document.getElementById("comments_pageid-" + pageid);
  var content = encodeURI(document.getElementById(reply_box_id).value);
  var params = "pageid=" + pageid + "&action=reply&content=" + content + "&reply_path=" + reply_path + "&username=user0";

  console.log("POST request to: " + api + " with params: " + params);

  var http_request = httpRequest();

  http_request.onreadystatechange = function() {

    if (http_request.readyState == 4) {
      refreshComments();
      http_request = null;
    }
  }

  http_request.open("POST", api, true);
  http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  http_request.send(params);
}

function postNewComment(caller) {
  var comment_box_id = 'comment-box';

  var comments_section = document.getElementById("comments_pageid-" + pageid);
  var content = encodeURI(document.getElementById(comment_box_id).value);
  var params = "pageid=" + pageid + "&action=postNewComment&content=" + content + "&username=user0";

  var http_request = httpRequest();

  console.log("POST request to: " + api + " with params: " + params);

  http_request.onreadystatechange = function() {

    if (http_request.readyState == 4) {
      refreshComments();
      http_request = null;
    }
  }

  http_request.open("POST", api, true);
  http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  http_request.send(params);
}

function deleteComment(caller) {
  if (confirm('Are you sure that you want to delete this comment?')) {
    var comments_section = document.getElementById("comments_pageid-" + pageid);
    var comment = findCommentRoot(caller);
    var ucid = comment.getAttribute('data-ucid');
    var reply_path = comment.getAttribute('data-reply-path');

    var params = "pageid=" + pageid + "&action=delete&ucid=" + ucid + "&reply_path=" + reply_path;

    console.log("POST request to: " + api + " with params: " + params);

    var http_request = httpRequest();

    http_request.onreadystatechange = function() {

      if (http_request.readyState == 4) {
        refreshComments();
        http_request = null;
      }
    }

    http_request.open("POST", api, true);
    http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
    http_request.send(params);
  }
}

function showEditArea(caller) {
  var comment = findCommentRoot(caller);
  var ucid = comment.getAttribute('data-ucid');
  var reply_path = comment.getAttribute('data-reply-path');

  var edit_area_id = 'edit-area-' + ucid;
  var edit_box_id = 'edit-box-' + ucid;
  var edit_button_id = 'edit-button-' + ucid;
  var cancel_button_id = 'edit-cancel-button-' + ucid;

  if (!document.getElementById(edit_area_id)) {
    var edit_area = document.createElement('div');
    edit_area.setAttribute('id', edit_area_id);

    var edit_box = document.createElement('textarea');
    edit_box.setAttribute('id', edit_box_id);
    edit_box.setAttribute('onkeyup', "autoGrow(this);");

    var url = api + "?pageid=" + pageid + "&action=getCommentMarkdown&ucid=" + ucid + "&reply_path=" + reply_path;
    var http_request = httpRequest();

    console.log("GET request to: " + url);

    http_request.onreadystatechange = function() {

      if (http_request.readyState == 4) {
        comment.getElementsByClassName('commentia-comment_content')[0].style.display = "none";
        comment.getElementsByClassName('commentia-edit_area')[0].appendChild(edit_area);
        comment.getElementsByClassName('commentia-edit_area')[0].style.display = "block";
        edit_box.innerHTML = http_request.responseText;
        document.getElementById(edit_box_id).style.height = document.getElementById(edit_box_id).scrollHeight + 'px';
        http_request = null;
      }
    }

    http_request.open("GET", url, true);
    http_request.send();

    edit_area.appendChild(edit_box);

    var edit_button = document.createElement('button');
    edit_button.innerHTML = 'edit';
    edit_button.setAttribute('id', edit_button_id);
    edit_button.setAttribute('onclick', 'editComment(this);');
    edit_area.appendChild(edit_button);

    var cancel_button = document.createElement('button');
    cancel_button.innerHTML = 'cancel';
    cancel_button.setAttribute('id', cancel_button_id);
    cancel_button.setAttribute('onclick', 'hideEditArea(this);');
    edit_area.appendChild(cancel_button);
  } else {
    comment.getElementsByClassName('commentia-comment_content')[0].style.display = "none";
    comment.getElementsByClassName('commentia-edit_area')[0].style.display = "block";
  }
}

function hideEditArea(caller) {
  var comment = findCommentRoot(caller);

  comment.getElementsByClassName('commentia-edit_area')[0].style.display = "none";
  comment.getElementsByClassName('commentia-comment_content')[0].style.display = "block";
}

function editComment(caller) {
  var comment = findCommentRoot(caller);
  var ucid = comment.getAttribute('data-ucid');
  var edit_box = document.getElementById('edit-box-' + ucid);
  var reply_path = comment.getAttribute('data-reply-path');

  var params = "pageid=" + pageid + "&action=edit&content=" + encodeURI(edit_box.value) + "&ucid=" + ucid + "&reply_path=" + reply_path;

  var http_request = httpRequest();

  console.log("POST request to: " + api + " with params: " + params);

  http_request.onreadystatechange = function() {

    if (http_request.readyState == 4) {
      refreshComments();
      http_request = null;
    }
  }

  http_request.open("POST", api, true);
  http_request.setRequestHeader("Content-type", "application/x-www-form-urlencoded");
  http_request.send(params);
}

function autoGrow(caller) {
  caller.style.height = "5px";
  caller.style.height = (caller.scrollHeight) + "px";
}
