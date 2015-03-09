<?php

	$app['{{ route_name_prefix }}.controller'] = $app->share(function() use ($app) {
		return new {{ entity_namespace }}\Controller\{{ entity_class }}Controller();
	});
	
	{% for route in routes %}
	$app->{{ route.method }}("{{ route.pattern }}", '{{ route_name_prefix }}.controller:{{ route.action }}')
		->bind('{{ route.name }}');
	{% endfor %}