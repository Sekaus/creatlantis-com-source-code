export function RulesAndPrivacyPopup(update = false) {
    var title = `You must go through and agree with the rules of our site and have read and accept our Terms of Service and Privacy Policy.`;

    if(update)
        title = `We have updated our Terms of Service and Privacy Policy. Please review them first before proceeding.`

    return /*html*/ `
            <div id="rules-and-privacy-popup">
                <div id="rules-and-privacy-popup-content">
                    <form id="rules-privacy-form" enctype="multipart/form-data" action="" method="POST">
                        <p class="big-text">${title}</p>
                        <br/>

                        <div>
                            <strong>I have read and agree to the Terms of Service and rules for using this website. </strong>
                            <input id="checkbox-a" type="checkbox" value="yes" required/>
                            <br/>
                            Read the Terms of Service and the rules here: <a href="./html_documents/terms_of_use.html"><u>Terms of Service</u></a>
                        </div>

                        <br/>

                        <div>
                            <strong>I have read and agree to the Privacy Policy for using this website. </strong>
                            <input id="checkbox-b" type="checkbox" value="yes" required/>
                            <br/>
                            Read the Privacy Policy here: <a href="./html_documents/privacy_policy.html"><u>Privacy Policy</u></a>
                        </div>

                        <br/>

                        <div id="rules-and-privacy-popup-submit-box">
                            <div class="vertical-hr"></div>

                            <input type="hidden" name="agreed" value="yes"/>

                            <button type="submit" class="submit">Continue</button>

                            <div class="vertical-hr"></div>
                        </div>
                    </form>
                </div>
            </div>
        `;
}

export const Themes = {
    green: 0,
    dark: 1,
    light: 2
};

export function ChangeTheme(theme) {
    $("body").removeClass();

    let themeClass = "";
    switch (theme) {
        case Themes.dark:
            themeClass = "dark-theme";
            break;
        case Themes.light:
            themeClass = "light-theme";
            break;
        case Themes.green:
            themeClass = "green-theme";
            break;
    }
    
    $("body").addClass(themeClass);
}

export function Startup() {
    $("body").get(0).style.setProperty("--star-rate-mask", `url("../images/5-stars.webp")`);
    $("body").get(0).style.setProperty('--upload-image', `url("../images/icons/uploadIcon.webp")`);
}