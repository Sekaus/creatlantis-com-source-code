<!DOCTYPE html>
<html>
    <head>
        <title>Main</title>
        <?php include_once("./html_elements/head.html"); ?>
    </head>
    <body>
        <script type="module">
            import { BBCodeRender } from '../js/text_formatter.js';

            $(document).ready(function () {
                const rawText = "[color=#ff0000]R[/color][color=#e93f15]a[/color][color=#d47a2a]i[/color][color=#bfad3f]n[/color][color=#aad655]b[/color][color=#94f16a]o[/color][color=#7ffe7f]w[/color] [color=#55e7aa]D[/color][color=#3fc6bf]a[/color][color=#2a98d4]s[/color][color=#1561e9]h[/color]";

                const formattedText = BBCodeRender(rawText);

                $('body').html(formattedText);
            });
        </script>
        <?php include_once("./html_elements/footer.html"); ?>
    </body>
</html>