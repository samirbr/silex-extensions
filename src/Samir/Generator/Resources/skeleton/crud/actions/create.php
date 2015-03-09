
    /**
     * Creates a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/create", name="{{ route_name_prefix }}_create")
     * @Method("POST")
     * @Template("{{ bundle }}:{{ entity }}:new.html.twig")
{% endif %}
     */
    public function createAction(Request $request, Application $app)
    {
			$entity  = new {{ entity_class }}();
			$form = $app['form.factory']->create(new {{ entity }}Type(), $entity, array());
			$form->bind($request);

			if ($form->isValid()) {
					$app['db.orm.em']->persist($entity);
					$app['db.orm.em']->flush();
	
					{% if 'show' in actions %}
					return $app->redirect($app['url_generator']->generate('{{ route_name_prefix }}_show', array(
						'id' => $entity->getId()
					)));
					{% endif %}
			}
			
			return $app['twig']->render('{{ entity_class }}\new.html.twig', array(
					'entity' => $entity,
					'form'   => $form->createView()
			));
    }
