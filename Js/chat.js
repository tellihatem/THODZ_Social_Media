var base_url = '/';

const searchBar = document.querySelector(".search input"),
searchIcon = document.querySelector(".search button"),
usersList = document.querySelector(".users-list");

searchIcon.onclick = ()=>{
  searchBar.classList.toggle("show");
  searchIcon.classList.toggle("active");
  searchBar.focus();
  if(searchBar.classList.contains("active")){
    searchBar.value = "";
    searchBar.classList.remove("active");
  }
}

searchBar.onkeyup = ()=>{
  let searchTerm = searchBar.value;
  if(searchTerm != ""){
    searchBar.classList.add("active");
  }else{
    searchBar.classList.remove("active");
  }
  $.ajax({
      type: "POST",
      contentType: false,
      cache: false,
      processData:false,
      contentType: 'application/x-www-form-urlencoded',
      url: base_url+"api/process.php?action=chatsearch",
      data: $.param({ "searchfor": searchTerm}),
      success: function(data) {
          if ((!$.trim(data)) || data == null){   
              //the data is null or blank
          }else{
              usersList.innerHTML = data;
          }
      },
  });
}

setInterval(()=>{
  $.ajax({
        type: "GET",
        contentType: false,
        cache: false,
        processData:false,
        contentType: 'application/x-www-form-urlencoded',
        url: base_url+"api/process.php?action=chatusers",
        success: function(data) {
            if ((!$.trim(data)) || data == null){   
                //the data is null or blank
            }else{
              if (!searchBar.classList.contains("active")){
                usersList.innerHTML = data;
              }
            }
        },
        error: function(data) {
            // Some error in ajax call
            alert("some Error");
        }
    });
},500); //we will run this function every 500ms


const searchnav = document.getElementById("SearchInputInside");
userslist_nav = document.getElementById("userList_search");
searchnav.onkeyup = ()=>{
  let searchTerm = searchnav.value;
  if(searchTerm != ""){
      $.ajax({
          type: "POST",
          contentType: false,
          cache: false,
          processData:false,
          contentType: 'application/x-www-form-urlencoded',
          url: base_url+"api/process.php?action=thodzsearch",
          data: $.param({ "searchfor": searchTerm}),
          success: function(data) {
              if ((!$.trim(data)) || data == null){   
                  //the data is null or blank
              }else{
                  userslist_nav.innerHTML = data;
              }
          },
      });
    }
}