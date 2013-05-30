<?php

namespace Knp\Bundle\PaginatorBundle\Subscriber;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Knp\Component\Pager\Event\ItemsEvent;
use Knp\Component\Pager\Event\AfterEvent;

class RequestSubscriber implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;

    /**
     * @var array
     */
    protected $params;

    /**
     * @var array The original values of the parameters in $_GET
     */
    protected $get;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $request The request object
     * @param array                                                     $params  The keys of the fields from the Paginator options to synchronize
     */
    public function __construct(ContainerInterface $container, $params = array())
    {
        $this->container = $container;
        $this->params = $params;
        $this->get = array();
    }

    /**
     * Updates the sorting parameter in $_GET to match the Request object
     */
    public function items(ItemsEvent $event)
    {
        foreach ($this->params as $option) {
            if (isset($event->options[$option])) {
                $name = $event->options[$option];

                if (null !== $this->container->get('request')->get($name)
                    && (!array_key_exists($name, $_GET) || $_GET[$name] !== $this->container->get('request')->get($name))
                ) {
                    $this->get[$name] = isset($_GET[$name]) ?: null;
                    $_GET[$name] = $this->container->get('request')->get($name);
                }
            }
        }
    }

    /**
     * Restore $_GET
     */
    public function after(AfterEvent $event)
    {
        foreach ($this->get as $name => $value) {
            if (null === $value) {
                unset($_GET[$name]);
            } else {
                $_GET[$name] = $value;
            }
        }

        $this->get = array();
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return array(
            'knp_pager.items' => array('items', 2),
            'knp_pager.after' => array('after', 1)
        );
    }
}
