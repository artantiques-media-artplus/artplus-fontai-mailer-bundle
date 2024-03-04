<?php
namespace Fontai\Bundle\MailerBundle\Message;


class Callback
{
  protected $entityClass;
  protected $id;
  protected $method;

  public function __construct(
    string $entityClass,
    int $id,
    string $method
  )
  {
    $this->setEntityClass($entityClass)
         ->setId($id)
         ->setMethod($method);
  }

  public function call()
  {
    $entity = $this->getEntity();

    if (!$entity)
    {
      return FALSE;
    }
    
    $entity->{$this->method}(new \DateTime());

    return $entity->save();
  }

  protected function setEntityClass(string $entityClass)
  {
    if (!class_exists($entityClass))
    {
      throw new \InvalidArgumentException(sprintf('Class %s does not exist.', $entityClass));
    }

    $this->entityClass = $entityClass;

    return $this;
  }

  protected function setId(int $id)
  {
    $this->id = $id;

    return $this;
  }

  protected function setMethod(string $method)
  {
    if (!method_exists($this->entityClass, $method))
    {
      throw new \InvalidArgumentException(sprintf('Class %s does not have method %s.', $this->entityClass, $method));
    }

    $this->method = $method;

    return $this;
  }

  protected function getEntity()
  {
    $query = call_user_func([$this->entityClass . 'Query', 'create']);

    return $query->findOneById($this->id);
  }
}