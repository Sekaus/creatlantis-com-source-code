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
    dark: "dark",
    light: "light",
    green: "green"
};

export function ChangeTheme(theme, updateServer = false) {
    $("body").removeClass();

    $(".theme-option").removeClass("selected-theme")

    let themeClass = "";
    switch (theme) {
        case Themes.dark:
            themeClass = "dark-theme";
            $("#dark-theme-option").addClass("selected-theme");
            break;
        case Themes.light:
            themeClass = "light-theme";
            $("#light-theme-option").addClass("selected-theme");
            break;
        case Themes.green:
            themeClass = "green-theme";
            $("#green-theme-option").addClass("selected-theme");
            break;
    }

    if(updateServer) {
        $.ajax({
                url: `./settings_handler.php`,
                method: "POST",
                data: {
                    command: "swap_theme",
                    theme: theme
                },
                success: function (response) {
                    location.reload();
                },
                error: function (xhr) {
                    let msg = 'Unknown error';
                    try { msg = JSON.parse(xhr.responseText).error; } catch (e) {}
                    alert("Settings update failed: " + msg);
                }
        });
    }
    else
        $("body").addClass(themeClass);
}