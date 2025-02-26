<script async src="https://telegram.org/js/telegram-widget.js?22"
        data-telegram-login="desmg_bot"
        data-size="large"
        data-onauth="onTelegramAuth(user);"
        data-request-access="write"></script>
<script type="text/javascript">
    function onTelegramAuth(user) {
        console.log(user);
    }
</script>
