{$subject = 'Вам было отказано в получении займа' scope=parent}

<html>
	<body>
		<p>{$order->firstname|escape} {$order->patronymic|escape} Вы оставляли заявку на получение займа на сайте {$settings->site_name|escape}.</p>
		<p>К сожалению Вам было отказано в получении займа.</p>
		{if $order->reject_reason}
        <p>Причина отказа: {$order->reject_reason}</p>
		{/if}
	</body>
</html>

