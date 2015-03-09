
    /**
     * Displays a form to create a new {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/new", name="{{ route_name_prefix }}_new")
     * @Method("GET")
     * @Template()
{% endif %}
     */
    public function newAction(Application $app)
    {
        $entity = new {{ entity_class }}();
				
				$form = $app['form.factory']->create(new {{ entity_class }}Type(), $entity, array());
				
				return $app['twig']->render('{{ entity_class }}\new.html.twig', array(
						'entity' => $entity,
						'form'   => $form->createView(),
				));
    }
