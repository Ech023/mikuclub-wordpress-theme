/**
 * 广告专用JS
 */
$(function () {

    let $pub3Container = $('.pub3-pc');
    let $pub3MobileContainer = $('.pub3-mobile');
    //如果存在 显示广告
    if ($pub3Container.length || $pub3MobileContainer.length) {
        pub3();
    }

    let $feijiTopPubContainer = $('.pub-feiji-top-pc');
    let $feijiTopPubMobileContainer = $('.pub-feiji-top-mobile');

    //如果存在 显示广告
    if ($feijiTopPubContainer.length || $feijiTopPubMobileContainer.length) {
        feijiTopPub();
    }


    let $pubContainer = $('.pub-js-game');
    let $pubPhoneContainer = $('.pub-js-game-phone');
    //如果存在 显示广告
    if ($pubContainer.length || $pubPhoneContainer.length) {
        js_game_pub();
    }


  


});






/**
 * A酱的绅士玩具屋
 * PC端+手机端
 */
 function pub3() {

    let index = Math.floor(Math.random() * 2) + 1;
    //默认链接
    let link = "https://shop119340084.taobao.com/shop/view_shop.htm?spm=a230r.1.14.4.75dc14ecGDLY4r&user_number_id=1965847533&mm_sycmid=1_144962_8d3c3900107ba43419eb50be47edd98d";
    let pcImgSrc  = 'https://cdn.mikuclub.fun/img/A酱的绅士玩具屋/pc'+index+'.webp?a=0';
    let mobileImgSrc = 'https://cdn.mikuclub.fun/img/A酱的绅士玩具屋/phone'+index+'.webp?a=0';

 
    //创建PC和手机端广告图
    let $pubContainer = $('.pub3-pc');
    let $pubMobileContainer = $('.pub3-mobile');

    let pcContent = `
        <a title="A酱的绅士玩具屋" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${pcImgSrc}" alt="A酱的绅士玩具屋"  skip_lazyload />
        </a>
    `;

    $pubContainer.append(pcContent);

    let mobileContent = `
        <a title="A酱的绅士玩具屋" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${mobileImgSrc}" alt="A酱的绅士玩具屋"  skip_lazyload />
        </a>
    `;

    $pubMobileContainer.append(mobileContent);
}



/*================================================================================================*/

/**
 * @deprecated 已弃用
 * 飞机杯顶部广告代码
 * PC端+手机端
 */
function feijiTopPub() {

    let index = Math.floor(Math.random() * 3) + 1;
    //默认链接
    let link = "https://segucrwj.taobao.com/";
    let pcImgSrc;
    let mobileImgSrc;

    switch (index) {
        case 1:
            pcImgSrc = 'https://img.alicdn.com/imgextra/i2/1974227597/O1CN01wsk4so25zTWdZdj2C_!!1974227597.jpg';
            mobileImgSrc = 'https://img.alicdn.com/imgextra/i2/1974227597/O1CN01JhpEyH25zTWV1KTU0_!!1974227597.jpg';
            break;
        case 2:
            pcImgSrc = 'https://img.alicdn.com/imgextra/i1/1974227597/O1CN01FeoMsJ25zTWZ5u3zJ_!!1974227597.jpg';
            mobileImgSrc = 'https://img.alicdn.com/imgextra/i4/1974227597/O1CN01SdkbjC25zTWWSY5UK_!!1974227597.jpg';
            break;
        case 3:
            pcImgSrc = 'https://img.alicdn.com/imgextra/i1/1974227597/O1CN01vHofqI25zTWb2BMiT_!!1974227597.jpg';
            mobileImgSrc = 'https://img.alicdn.com/imgextra/i3/1974227597/O1CN01A1xqCY25zTWYS6rIA_!!1974227597.jpg';
            link = 'https://item.taobao.com/item.htm?spm=a1z10.3-c-s.w4023-15882287196.2.7f7f64a5RIjwRv&id=610691535269';
            break;
    }

    //创建PC和手机端广告图
    let $feijiTopPubContainer = $('.pub-feiji-top-pc');
    let $feijiTopPubMobileContainer = $('.pub-feiji-top-mobile');

    let pcContent = `
        <a title="涩谷玩具" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${pcImgSrc}" alt="涩谷玩具"  skip_lazyload />
        </a>
    `;

    $feijiTopPubContainer.append(pcContent);

    let mobileContent = `
        <a title="涩谷玩具" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${mobileImgSrc}" alt="涩谷玩具"  skip_lazyload />
        </a>
    `;

    $feijiTopPubMobileContainer.append(mobileContent);
}





/**
 * @deprecated 已弃用
 * JS Game
 * PC端+手机端
 */
function js_game_pub() {

    let index = Math.floor(Math.random() * 2) + 1;
    //默认链接
    let link;
    let pcImgSrc;
    let mobileImgSrc;

    switch (index) {
        case 1:
            pcImgSrc = 'https://cdn.mikuclub.fun/img/JSGame/pc-1.webp';
            mobileImgSrc = 'https://cdn.mikuclub.fun/img/JSGame/phone-1.webp';
            link = 'http://yujipop.com/igwg/index.html?ag=mikuclub';
            break;

        case 2:
            pcImgSrc =  'https://cdn.mikuclub.fun/img/JSGame/pc-2.webp';
            mobileImgSrc =  'https://cdn.mikuclub.fun/img/JSGame/phone-2.webp';
            link = 'https://oo1rcd.live';
            break;

    }

    //创建PC和手机端广告图
    let $pubContainer = $('.pub-js-game');
    let $pubPhoneContainer = $('.pub-js-game-phone');

    let pcContent = `
        <a title="JS GAME" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${pcImgSrc}" alt="JS GAME"  skip_lazyload />
        </a>
    `;

    $pubContainer.append(pcContent);

    let mobileContent = `
        <a title="JS GAME" href="${link}"  rel="nofollow" target="_blank">
            <img class="img-fluid w-100" src="${mobileImgSrc}" alt="JS GAME"  skip_lazyload />
        </a>
    `;

    $pubPhoneContainer.append(mobileContent);
}