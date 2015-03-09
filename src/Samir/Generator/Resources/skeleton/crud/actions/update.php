
    /**
     * Edits an existing {{ entity }} entity.
     *
{% if 'annotation' == format %}
     * @Route("/{id}/update", name="{{ route_name_prefix }}_update")
     * @Method("POST")
     * @Template("{{ bundle }}:{{ entity }}:edit.html.twig")
{% endif %}
     */
    public function updateAction(Request $request, $id, Application $app)
    {
      $entity = $app['db.orm.em']->getRepository('{{ namespace }}\Entity\{{ entity }}')
				->find($id);

			if ( ! $entity) {
				return $app->abort(404, 'Unable to find {{ entity_class }} entity.');
			}

			$deleteForm = $this->createDeleteForm($id);
			$editForm = $app['form.factory']->create(new {{ entity_class }}Type(), $entity, array());
			$editForm->bind($request);

			if ($editForm->isValid()) {
					$app['db.orm.em']->persist($entity);
					$app['db.orm.em']->flush();
					
					return $app->redirect($app['url_generator']->generate('{{ route_name_prefix }}_edit', array(
						'id' => $id
					)));
			}

			return $app['twig']->render('{{ entity_class }}\edit.html.twig', array(
					'entity'      => $entity,
					'edit_form'   => $editForm->createView(),
					'delete_form' => $deleteForm->createView(),
			));
    }
