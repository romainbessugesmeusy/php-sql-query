SQL Query Abstraction Layer
=============

The **php-sql-query** package aims to provide a consistent abstraction layer for SQL query construction. It's divided in two parts: 

1. 	abstract query components *(columns, tables, limit, groups)* 
2. renderers *(MySQL, PgSql, Sql Server)*

Creating a select is really straightforward: 

```php
$select = new \RBM\SqlQuery\Select();
$select->setTable("project");
$select->setColumns(["project_id", "name"]);
$select->limit(0, 10);
```
To output the correct query according to the database system in use, you'll have to instanciate the renderer, and call its render method:

```php	
$renderer = new \RBM\SqlQuery\RendererAdapter\MySql();
echo $renderer->render($select);
```
This will print the following string:

```php	
SELECT
	`project`.`project_id`
	, `project`.`name`
FROM
	`project`
LIMIT 0, 10
```	
Hopefully, you don't have to be *that* verbose to get the job done. 

```php
// do this once
\RBM\SqlQuery\Select::setDefaultRenderer("\RBM\SqlQuery\RendererAdapter");
// […]
$select = new \RBM\SqlQuery\Select("project", ["project_id", "name"]);
echo $select;
```

Filtering
-----------
In this API there's no ``->where()`` method, instead, there's ``->filter()`` which returns a ``\RBM\SqlQuery\Filter`` object. This object provides some basic methods : 

* `equals($column, $value)`
* `greaterThan($colum, $value)`
* `lowerThanEquals($colum, $value)`
* `isNull($colum)`
* …

There are also less basic methods such as :

* `in($column, $values)`
* `between($column, $a, $b)`

All of these are chainable for readability and effortless development sake.

```php
	$select = new Select('project', ['project_id', 'name']);
	$select->filter()
		   		->equals('owner_id', 1)
		   		->isNull('date_deleted');
```

Of course, you can determine which operator you want, and nest clauses:

```php
	$select->filter()->subFilter()
					->operator('OR')
					->equals('status', 'DRAFT')
					->equals('status', 'PUBLISHED');
```
					
Result:
```sql
	SELECT
		project.project_id
		, project.name
	FROM
		project	
	WHERE
		( project.owner_id = 1 )
	 AND ( project.date_deleted IS NULL )
	 AND (
	 		( project.status = 'DRAFT' )
	 		OR ( project.status = 'PUBLISHED' )
	 	)
```

SELECT & JOINS
--------------
A **JOIN** is a **SELECT**. In fact, there is no `\RBM\SqlQuery\Join` class and there won't be.

	$select = new Select('project', ['project_id', 'name']);
	$owner = $select->join('user', 'owner_id', 'user_id');
	print_r($owner);
	
Gives us 
	
	RBM\SqlQuery\Select Object
	(
    	[_table:protected] => RBM\SqlQuery\Table Object
        	(
            	[_name:protected] => user
	…
	
The most effective advantage of considering J0IN as SELECT is reusability. Let say you have an entity that provides some selects. 

	class UserEntity 
	{
	
		public function getSelectForActiveUsers()
		{
			$select = new Select('user');
			$select->filter()->equals('active', 1);
			return $select;
		}
		
	}	
	
	class ProjectEntity 
	{

		public function getSelectForProjectsOfActiveUsers()
		{
			$userEntity = new UserEntity();
			$select = new Select('project');
			$select->addJoin($userEntity->getSelectForActiveUsers(), 'owner_id', 'user_id');
		}	
	}


Overloading and inheritance
------------
One intersting feature of this package is that it can be extended to customize the query and filter layer. The finality of inheritance is to objectify the query construction, by making it fluent and business oriented.

###Select

While extending selects, you'll discover that the first obvious usage is to define shortcuts for joins: 

	class ProjectSelect extends \RBM\SqlQuery\Select
	{
		// overloading the table (simple way)
		protected $_table = 'project';
	
		public function owner()
		{
			return $this->join('user', 'owner_id', 'user_id');
		}
	}

Usage:

	$projects = new ProjectSelect();
	$projects->owner()->filter()->equals('user_id', 1);
	
###Filter

Creating a filter for our project table is straightforward: 
	
	class ProjectFilter extends \RBM\SqlQuery\Filter
	{
		public function deleted($deleted)
		{
			return ($deleted) $this->isNull('date_deleted') : $this->isNotNull('date_deleted');
		}
	}

To use this filter, you'll have to modified the `filterClass` property from the select:
	
	$projects = new ProjectSelect();
	$projects->filter()->deleted(true);
