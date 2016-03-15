<?php
namespace Topxia\AdminBundle\Controller;

use Topxia\Common\Paginator;
use Topxia\Common\ArrayToolkit;
use Symfony\Component\HttpFoundation\Request;

class OpenCourseController extends BaseController
{
    public function indexAction(Request $request, $filter)
    {
        $conditions = $request->query->all();

        $conditions['types'] = array('open', 'liveOpen');

        if (!empty($conditions['tags'])) {
            $tags               = $conditions['tags'];
            $tagNames           = explode(",", $conditions['tags']);
            $tagIds             = ArrayToolkit::column($this->getTagService()->findTagsByNames($tagNames), 'id');
            $conditions['tags'] = $tagIds;
        } else {
            unset($conditions['tags']);
        }

        if (empty($conditions["categoryId"])) {
            unset($conditions["categoryId"]);
        }

        if (empty($conditions["title"])) {
            unset($conditions["title"]);
        }

        if (empty($conditions["creator"])) {
            unset($conditions["creator"]);
        }

        $count = $this->getCourseService()->searchCourseCount($conditions);

        $paginator = new Paginator($this->get('request'), $count, 20);
        $courses   = $this->getCourseService()->searchCourses(
            $conditions,
            null,
            $paginator->getOffsetCount(),
            $paginator->getPerPageCount()
        );

        $categories = $this->getCategoryService()->findCategoriesByIds(ArrayToolkit::column($courses, 'categoryId'));

        $users = $this->getUserService()->findUsersByIds(ArrayToolkit::column($courses, 'userId'));

        $default = $this->getSettingService()->get('default', array());

        return $this->render('TopxiaAdminBundle:OpenCourse:index.html.twig', array(
            'tags'       => empty($tags) ? '' : $tags,
            'courses'    => $courses,
            'categories' => $categories,
            'users'      => $users,
            'paginator'  => $paginator,
            'default'    => $default,
            'classrooms' => array(),
            'filter'     => $filter
        ));
    }

    protected function getTagService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.TagService');
    }

    protected function getCourseService()
    {
        return $this->getServiceKernel()->createService('Course.CourseService');
    }

    protected function getSettingService()
    {
        return $this->getServiceKernel()->createService('System.SettingService');
    }

    protected function getCategoryService()
    {
        return $this->getServiceKernel()->createService('Taxonomy.CategoryService');
    }
}
