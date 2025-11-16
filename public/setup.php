<script type="module">
    import { Startup, ChangeTheme, Themes, RulesAndPrivacyPopup } from "./js/setup.js";

    $(document).ready(() => {
        Startup();
        ChangeTheme(Themes.dark);
        
    });
</script>