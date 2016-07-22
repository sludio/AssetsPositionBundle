<?php

namespace Assets\PositionBundle\Controller;

use Assets\PositionBundle\Services\PositionHandler;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\RedirectResponse;

class PositionAdminController extends CRUDController
{
    /**
     * Move element
     *
     * @param string $position
     */
    public function moveAction($position)
    {
        if (!$this->admin->isGranted('EDIT')) {
            $this->addFlash('sonata_flash_error', 'error');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $object = $this->admin->getSubject();

        /** @var PositionHandler $positionService */
        $positionService = $this->get('assets_position.position');

        $entity = \Doctrine\Common\Util\ClassUtils::getClass($object);

        $lastPosition = $positionService->getLastPosition($entity);

        $position = $positionService->getPosition($object, $position, $lastPosition);

        $setter = sprintf('set%s', ucfirst($positionService->getPositionFieldByEntity($entity)));

        if (!method_exists($object, $setter)) {
            throw new \LogicException(
                sprintf(
                    '%s does not implement ->%s() to set the desired position.',
                    $object,
                    $setter
                )
            );
        }

        $object->{$setter}($position);
        $this->admin->update($object);

        if ($this->isXmlHttpRequest()) {
            return $this->renderJson(array(
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($object)
            ));
        }

        $this->addFlash('sonata_flash_success', 'success');

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

}
