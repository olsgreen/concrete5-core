<?php
namespace Concrete\Core\User;

use Concrete\Core\Search\ItemList\Database\AttributedItemList as DatabaseItemList;
use Concrete\Core\Search\Pagination\Pagination;
use Concrete\Core\Support\Facade\Application;
use Concrete\Core\User\Group\Group;
use Pagerfanta\Adapter\DoctrineDbalAdapter;

class UserList extends DatabaseItemList
{
    protected function getAttributeKeyClassName()
    {
        return '\\Concrete\\Core\\Attribute\\Key\\UserKey';
    }

    /**
     * Columns in this array can be sorted via the request.
     *
     * @var array
     */
    protected $autoSortColumns = [
        'u.uName',
        'u.uEmail',
        'u.uDateAdded',
        'u.uLastLogin',
        'u.uNumLogins',
        'u.uLastOnline',
    ];

    /**
     * Whether to include inactive users.
     *
     * @var bool
     */
    protected $includeInactiveUsers = false;

    /**
     * Whether to include unvalidated users.
     *
     * @var bool
     */
    protected $includeUnvalidatedUsers = false;

    protected function setBaseQuery()
    {
        $sql = '';
        if ($this->sortUserStatus) {
            // When uStatus column is selected, we also get the "status" column for
            // multilingual sorting purposes.
            $sql =
                ", CASE WHEN u.uIsActive = 1 THEN '" . t('Active') . "' " .
                "WHEN u.uIsValidated = 1 AND u.uIsActive = 0 THEN '" . t('Inactive') . "' " .
                "ELSE '" . t('Unvalidated') . "' END AS uStatus";
        }
        $this->setQuery('SELECT DISTINCT u.uID, u.uName' . $sql . ' FROM Users u ');
    }

    /**
     * The total results of the query.
     *
     * @return int
     */
    public function getTotalResults()
    {
        $query = $this->deliverQueryObject();

        return $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct u.uID)')->setMaxResults(1)->execute()->fetchColumn();
    }

    /**
     * Gets the pagination object for the query.
     *
     * @return Pagination
     */
    protected function createPaginationObject()
    {
        $adapter = new DoctrineDbalAdapter($this->deliverQueryObject(), function ($query) {
            $query->resetQueryParts(['groupBy', 'orderBy'])->select('count(distinct u.uID)')->setMaxResults(1);
        });
        $pagination = new Pagination($this, $adapter);

        return $pagination;
    }

    /**
     * @var UserInfoRepository|null
     */
    private $userInfoRepository = null;

    /**
     * @param UserInfoRepository $value
     *
     * @return $this;
     */
    public function setUserInfoRepository(UserInfoRepository $value)
    {
        $this->userInfoRepository = $value;

        return $this;
    }

    /**
     * @return UserInfoRepository
     */
    public function getUserInfoRepository()
    {
        if ($this->userInfoRepository === null) {
            $this->userInfoRepository = Application::getFacadeApplication()->make(UserInfoRepository::class);
        }

        return $this->userInfoRepository;
    }

    /**
     * @param $queryRow
     *
     * @return \Concrete\Core\User\UserInfo
     */
    public function getResult($queryRow)
    {
        return $this->getUserInfoRepository()->getByID($queryRow['uID']);
    }

    /**
     * similar to get except it returns an array of userIDs
     * much faster than getting a UserInfo object for each result if all you need is the user's id.
     *
     * @return array $userIDs
     */
    public function getResultIDs()
    {
        $results = $this->executeGetResults();
        $ids = [];
        foreach ($results as $result) {
            $ids[] = $result['uID'];
        }

        return $ids;
    }

    public function createQuery()
    {
        $this->query->select('u.uID')
            ->from('Users', 'u')
            ->leftJoin('u', 'UserSearchIndexAttributes', 'ua', 'u.uID = ua.uID')
            ->groupBy('u.uID')
        ;
    }

    public function finalizeQuery(\Doctrine\DBAL\Query\QueryBuilder $query)
    {
        if (!$this->includeInactiveUsers) {
            $query->andWhere('u.uIsActive = :uIsActive');
            $query->setParameter('uIsActive', true);
        }
        if (!$this->includeUnvalidatedUsers) {
            $query->andWhere('u.uIsValidated != 0');
        }

        return $query;
    }

    public function includeInactiveUsers()
    {
        $this->includeInactiveUsers = true;
    }

    public function includeUnvalidatedUsers()
    {
        $this->includeUnvalidatedUsers = true;
    }

    /**
     * Explicitly filters by whether a user is active or not. Does this by setting "include inactive users"
     * to true, THEN filtering them in our out. Some settings here are redundant given the default settings
     * but a little duplication is ok sometimes.
     *
     * @param $val
     */
    public function filterByIsActive($isActive)
    {
        $this->includeInactiveUsers();
        $this->query->andWhere('u.uIsActive = :uIsActive');
        $this->query->setParameter('uIsActive', $isActive);
    }

    /**
     * Filter list by whether a user is validated or not.
     *
     * @param bool $isValidated
     */
    public function filterByIsValidated($isValidated)
    {
        $this->includeInactiveUsers();
        if (!$isValidated) {
            $this->includeUnvalidatedUsers();
            $this->query->andWhere('u.uIsValidated = :uIsValidated');
            $this->query->setParameter('uIsValidated', $isValidated);
        }
    }

    public function sortByStatus($dir = 'asc')
    {
        $this->sortUserStatus = 1;
        parent::sortBy('uStatus', $dir);
    }

    /**
     * Filter list by user name.
     *
     * @param $username
     */
    public function filterByUserName($username)
    {
        $this->query->andWhere('u.uName = :uName');
        $this->query->setParameter('uName', $username);
    }

    /**
     * Filter list by user name but as a like parameter.
     *
     * @param $username
     */
    public function filterByFuzzyUserName($username)
    {
        $this->query->andWhere(
            $this->query->expr()->like('u.uName', ':uName')
        );
        $this->query->setParameter('uName', $username . '%');
    }

    /**
     * Filters keyword fields by keywords (including username, email and attributes).
     *
     * @param $keywords
     */
    public function filterByKeywords($keywords)
    {
        $expressions = [
            $this->query->expr()->like('u.uName', ':keywords'),
            $this->query->expr()->like('u.uEmail', ':keywords'),
        ];

        $keys = \Concrete\Core\Attribute\Key\UserKey::getSearchableIndexedList();
        foreach ($keys as $ak) {
            $cnt = $ak->getController();
            $expressions[] = $cnt->searchKeywords($keywords, $this->query);
        }
        $expr = $this->query->expr();
        $this->query->andWhere(call_user_func_array([$expr, 'orX'], $expressions));
        $this->query->setParameter('keywords', '%' . $keywords . '%');
    }

    /**
     * Filters the user list for only users within the provided group.  Accepts an instance of a group object or a string group name.
     *
     * @param \Group | string $group
     * @param bool $inGroup
     */
    public function filterByGroup($group = '', $inGroup = true)
    {
        if (!($group instanceof \Concrete\Core\User\Group\Group)) {
            $group = \Concrete\Core\User\Group\Group::getByName($group);
        }
        $this->filterByInAnyGroup([$group], $inGroup);
    }

    /**
     * Filters the user list for only users within at least one of the provided groups.
     *
     * @param \Concrete\Core\User\Group\Group[]|\Generator $groups
     * @param bool $inGroups Set to true to search users that are in at least in one of the specified groups, false to search users that aren't in any of the specified groups
     */
    public function filterByInAnyGroup($groups, $inGroups = true)
    {
        $where = null;
        foreach ($groups as $group) {
            if ($group instanceof \Concrete\Core\User\Group\Group) {
                $uniqueID = str_replace('.', '_', uniqid($group->getGroupID() . '_', true));
                $joinTable = 'ug' . $uniqueID;
                $groupTable = 'g' . $uniqueID;
                $path = $group->getGroupPath();
                $this->query->leftJoin('u', 'UserGroups', $joinTable, 'u.uID = ' . $joinTable . '.uID');
                $this->query->leftJoin($joinTable, 'Groups', $groupTable, '(' . $joinTable . '.gID = ' . $groupTable . '.gID and ' . $groupTable . '.gPath like :gPath' . $uniqueID . ')');
                $this->query->setParameter('gPath' . $uniqueID, $path . '%');
                if ($inGroups) {
                    if ($where === null) {
                        $where = $this->query->expr()->orX();
                    }
                    $where->add($groupTable . '.gID is not null');
                } else {
                    if ($where === null) {
                        $where = $this->query->expr()->andX();
                    }
                    $where->add($groupTable . '.gID is null');
                }
            }
        }
        if ($where === null) {
            if ($inGroups) {
                $this->query->andWhere('1 = 0');
            }
        } else {
            $this->query->andWhere($where);
        }
    }

    /**
     * Filters by date added.
     *
     * @param string $date
     */
    public function filterByDateAdded($date, $comparison = '=')
    {
        $this->query->andWhere($this->query->expr()->comparison('u.uDateAdded', $comparison, $this->query->createNamedParameter($date)));
    }

    /**
     * Filters by Group ID.
     */
    public function filterByGroupID($gID)
    {
        $group = Group::getByID($gID);
        $this->filterByGroup($group);
    }

    public function filterByNoGroup()
    {
        $this->query->leftJoin('u', 'UserGroups', 'ugex', 'u.uID = ugex.uID');
        $this->query->andWhere('ugex.gID is null');
    }

    public function sortByUserID()
    {
        $this->query->orderBy('u.uID', 'asc');
    }

    public function sortByUserName()
    {
        $this->query->orderBy('u.uName', 'asc');
    }

    public function sortByDateAdded()
    {
        $this->query->orderBy('u.uDateAdded', 'desc');
    }
}
