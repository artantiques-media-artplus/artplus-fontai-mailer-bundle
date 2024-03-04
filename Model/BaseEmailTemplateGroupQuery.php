<?php
namespace Fontai\Bundle\MailerBundle\Model;

use Propel\Runtime\ActiveQuery\Criteria;
use Propel\Runtime\ActiveQuery\ModelCriteria;
use Propel\Runtime\Connection\ConnectionInterface;


abstract class BaseEmailTemplateGroupQuery extends ModelCriteria
{
  protected function preSelect(ConnectionInterface $con)
  {
    $this
    ->orderByPriority(Criteria::DESC)
    ->orderByName();
  }
}