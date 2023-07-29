
/*$(function () {
*/

/*
//如果当前域名为 cc
if (location.host === 'www.mikuclub.cc') {
    //重定向到online
    let url = location.href.replace('www.mikuclub.cc', 'www.mikuclub.online');
    location.replace(url);
}*/


 //如果当前域名为 online 或者 cc
 if (location.host === 'www.mikuclub.online' || location.host === 'www.mikuclub.cc' ) {
    //重定向到win
    let url = location.href.replace('www.mikuclub.online', 'www.mikuclub.win').replace('www.mikuclub.cc', 'www.mikuclub.win');
    location.replace(url);
}



/*

});*/