
    /**
     * Lists all {{ entity }} entities.
     *
{% if 'annotation' == format %}
     * @Route("/", name="{{ route_name_prefix }}")
     * @Method("GET")
     * @Template()
{% endif %}
     */
    public function indexAction(Application $app)
    {
			$entities = $app['db.orm.em']->getRepository('{{ namespace }}\Entity\{{ entity }}')
				->findAll();
			
			return $app['twig']->render('{{ entity_class }}\index.html.twig', array(
					'entities' => $entities,
			));
    }
