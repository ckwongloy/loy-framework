<?php

declare(strict_types=1);

namespace Domain\__DOMAIN__\Http\Port__NAMESPACE__;

use Dof\Framework\OFB\Pipe\Paginate;
use Dof\Framework\OFB\Pipe\Sorting;
use Domain\__DOMAIN__\Http\ERR;
use Domain\__DOMAIN__\Service\CRUD\Create__NAME__;
use Domain\__DOMAIN__\Service\CRUD\Delete__NAME__;
use Domain\__DOMAIN__\Service\CRUD\Update__NAME__;
use Domain\__DOMAIN__\Service\CRUD\Show__NAME__;
use Domain\__DOMAIN__\Service\CRUD\List__NAME__;

/**
 * @Author(name@group.com)
 * @Version(v1)
 * @Auth(0)
 * @_Group(__DOMAIN__/__NAME__)
 * @Route(__NAME__)
 * @Model(Domain\__DOMAIN__\Entity\__NAME__)
 * @Assembler(Domain\__DOMAIN__\Assembler\__NAME__)
 * @MimeOut(json)
 */
class __NAME__
{
    /**
     * @Title(__NAME__ ID)
     * @Type(Pint)
     */
    private $id;

    /**
     * @Title(Parameter Title)
     * @Type(String)
     */
    private $param1;

    /**
     * @Title(Create Resource __NAME__)
     * @Route(/)
     * @Verb(POST)
     * @Argument(param1)
     * @HeaderStatus(201){Created Success}
     */
    public function create(Create__NAME__ $service)
    {
        // $service->error(ERR::_, 500);
        // extract(pargvs());

        return $service
            ->setParam1(port('argument')->param1)
            ->execute();
    }

    /**
     * @Title(Delete Resource __NAME__)
     * @Route({id})
     * @Verb(DELETE)
     * @Argument(id){inroute}
     * @HeaderStatus(204){Deleted Success}
     * @Assembler(_)
     */
    public function delete(int $id, Delete__NAME__ $service)
    {
        // $service->error(ERR::_, 404);

        $service->setId($id)->execute();

        response()->setStatus(204);
    }

    /**
     * @Title(Update Resource __NAME__)
     * @Route({id})
     * @Verb(PUT)
     * @Argument(id){inroute}
     * @Argument(param1){need:0}
     */
    public function update(int $id, Update__NAME__ $service)
    {
        // $service->error(ERR::_, 500);

        return $service
            ->setId($id)
            ->setParam1(port('argument')->param1)
            ->execute();
    }

    /**
     * @Title(Show Resource __NAME__ Detail)
     * @Route({id})
     * @Verb(GET)
     * @Argument(id){inroute}
     */
    public function show(int $id, Show__NAME__ $service)
    {
        return $service->setId($id)->execute();
    }

    /**
     * @Title(List Resource __NAME__ with Pagination)
     * @Route(/)
     * @Verb(GET)
     * @PipeIn(Paginate)
     * @PipeIn(Sorting)
     * @WrapOut(Dof\Framework\OFB\Wrapper\Pagination)
     */
    public function list(List__NAME__ $service)
    {
        $paginate = route('params')->pipe->get(Paginate::class);
        $sorting = route('params')->pipe->get(Sorting::class);

        return $service
            ->setPage($paginate->page)
            ->setSize($paginate->size)
            ->setSortField($sort->field)
            ->setSortOrder($sort->order)
            ->execute();
    }
}
