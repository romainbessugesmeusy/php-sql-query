<?php

require './bootstrap.php';

use RBM\SqlQuery\Select;
use RBM\SqlQuery\Filter;
use RBM\SqlQuery\OrderBy;
use RBM\SqlQuery\Token;

$projectDefinition = "
CREATE TABLE `project` (
`project_id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `client_id` int(10) unsigned NOT NULL,
  `date_opened` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `date_modified` timestamp NULL DEFAULT NULL,
  `date_closed` timestamp NULL DEFAULT NULL,
  `name` varchar(200) NOT NULL DEFAULT '',
  `identifier` varchar(200) NOT NULL DEFAULT '',
  `position` int(10) unsigned NOT NULL DEFAULT '1',
  PRIMARY KEY (`project_id`),
  UNIQUE KEY `client_id__identifier` (`client_id`,`identifier`),
  CONSTRAINT `project_ibfk_1` FOREIGN KEY (`client_id`) REFERENCES `client` (`client_id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8;
";

$userDefinition = "
CREATE TABLE `user` (
  `user_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `email` varchar(255) NOT NULL DEFAULT '',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
";

$projectUserDefinition = "
CREATE TABLE `project_user` (
  `project_id` int(10) unsigned NOT NULL,
  `user_id` int(10) unsigned NOT NULL,
  `project_role_id` int(10) unsigned NOT NULL,
  `date_added` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`project_id`,`user_id`,`project_role_id`),
  KEY `user_id` (`user_id`),
  KEY `project_role_id` (`project_role_id`),
  CONSTRAINT `project_user_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `project` (`project_id`),
  CONSTRAINT `project_user_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `user` (`user_id`),
  CONSTRAINT `project_user_ibfk_3` FOREIGN KEY (`project_role_id`) REFERENCES `project_role` (`project_role_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;
";

$projectRoleDefinition = "
CREATE TABLE `project_role` (
  `project_role_id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL DEFAULT '',
  PRIMARY KEY (`project_role_id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8;
";

/**
 * Class ProjectSelect
 *
 * To gain advantage of IDE's autocompletion, force the PHP Doc return type of Filter
 * @method ProjectFilter filter
 */
class ProjectSelect extends Select
{
    protected $_table = 'project';
    protected $_filterClass = 'ProjectFilter';

    public function userByRole($role)
    {
        $projectUser = $this->join([$role . '_project_user' => 'project_user'], 'project_id');
        $projectUser->join('project_role', 'project_role_id')->joinCondition()->eq('name', $role);
        return $projectUser->join([$role . '_user' => 'user'], 'user_id');
    }

    public function owner()
    {
        return $this->userByRole('owner');
    }

    public function client()
    {
        return $this->join('client', 'client_id');
    }

    public function lastModified()
    {
        $this->limit(0, 1);
        $this->orderBy('date_modified', OrderBy::DESC);
        return $this;
    }
}


class ProjectFilter extends Filter
{
    protected $_table = 'project';

    public function clientId($clientId)
    {
        $this->eq("client_id", $clientId);
    }

    public function closed($isClosed)
    {
        $f = $this->subFilter()
            ->conjonction(Filter::CONJONCTION_OR)
            ->isNull('date_closed', $isClosed);

        if ($isClosed) {
            $f->lowerThan('date_closed', Token::CURRENT_TIMESTAMP);
        } else {
            $f->greaterThan('date_closed', Token::CURRENT_TIMESTAMP);
        }
        return $this;
    }
}

$project = new ProjectSelect();
$project->filter()->closed(false);
$project->client()->filter()->like("company_name", "Kloook");
$project->lastModified();
$project->userByRole("owner")->filter()->eq("email", "roma'in@kloook.com");

printQuery($project);