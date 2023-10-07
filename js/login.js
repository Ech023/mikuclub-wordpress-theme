
/*$(function () {
*/



//如果当前域名为 online 或者 cc
if (location.host === SITE_DOMAIN.www_mikuclub_online || location.host === SITE_DOMAIN.www_mikuclub_cc) {
    //重定向到win
    let url = location.href
        .replace(SITE_DOMAIN.www_mikuclub_online, SITE_DOMAIN.www_mikuclub_win)
        .replace(SITE_DOMAIN.www_mikuclub_cc, SITE_DOMAIN.www_mikuclub_online);
    location.replace(url);
}



/*

});*/