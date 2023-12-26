
/*$(function () {
*/
const LOGIN_SITE_DOMAIN = {
    www_mikuclub_online: 'www.mikuclub.online',
    www_mikuclub_cc: 'www.mikuclub.cc',
    www_mikuclub_win: 'www.mikuclub.win',
    www_mikuclub_eu: 'www.mikuclub.eu',
    www_mikuclub_uk: 'www.mikuclub.uk',
  
}


//如果当前域名为 online 或者 cc
if (location.host === LOGIN_SITE_DOMAIN.www_mikuclub_online || location.host === LOGIN_SITE_DOMAIN.www_mikuclub_cc) {
    //重定向到win
    let url = location.href
        .replace(LOGIN_SITE_DOMAIN.www_mikuclub_online, LOGIN_SITE_DOMAIN.www_mikuclub_win)
        .replace(LOGIN_SITE_DOMAIN.www_mikuclub_cc, LOGIN_SITE_DOMAIN.www_mikuclub_online);
    location.replace(url);
}



/*

});*/