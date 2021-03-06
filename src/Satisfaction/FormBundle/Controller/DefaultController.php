<?php

namespace Satisfaction\FormBundle\Controller;

use Satisfaction\FormBundle\Form\Type\TicketType;
use Satisfaction\FormBundle\Form\Type\TicketTypeNew;
use Satisfaction\FormBundle\Form\Type\TicketTypeView;
use Satisfaction\FormBundle\Form\Type\TicketTypeEdit;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Satisfaction\FormBundle\Entity\Ticket;
use Symfony\Component\HttpFoundation\Request;

class DefaultController extends Controller
{
    /**
     * @param $item
     * @return Ticket
     */
    function setTheTicket($item)
    {
        $em = $this->getDoctrine()->getManager();
        $ticket = $em->getRepository('SatisfactionFormBundle:Ticket')->findOneById($item['id']);

        if (!$ticket) {
            throw $this->createNotFoundException(
                'Pas de ticket avec un id '.$item['id']
            );
        }

        $ticket->setId($item['id']);
        $ticket->setNumTicket($item['NumTicket']);
        $ticket->setSujet($item['Sujet']);
        $ticket->setDescription($item['Description']);
        $ticket->setSatisfaction($item['Satisfaction']);
        $ticket->setConformite($item['Conformite']);
        $ticket->setAccompagnement($item['Accompagnement']);
        $ticket->setDelais($item['Delais']);
        $ticket->setCommentaires($item['Commentaires']);
        $ticket->setStatus('Answered');
        $em->flush();

        return $ticket;
    }

    /**
     * @param $numticket
     * @return Ticket
     */
    function getTheTicket($numticket)
    {

        $repository = $this->getDoctrine()
            ->getRepository('SatisfactionFormBundle:Ticket');

        $ticket_look = $repository->findByNumticket($numticket);

        $id = $ticket_look[0]->getId();
        $numticket = $ticket_look[0]->getNumticket();
        $sujet = $ticket_look[0]->getSujet();
        $description = $ticket_look[0]->getDescription();
        $satisfaction = $ticket_look[0]->getSatisfaction();
        $conformite = $ticket_look[0]->getConformite();
        $accompagnement = $ticket_look[0]->getAccompagnement();
        $delais = $ticket_look[0]->getDelais();
        $commentaires = $ticket_look[0]->getCommentaires();

        $ticket = new Ticket();

        $ticket->setId($id);
        $ticket->setNumTicket($numticket);
        $ticket->setSujet($sujet);
        $ticket->setDescription($description);
        $ticket->setSatisfaction($satisfaction);
        $ticket->setConformite($conformite);
        $ticket->setAccompagnement($accompagnement);
        $ticket->setDelais($delais);
        $ticket->setCommentaires($commentaires);

        return $ticket;
    }

    /**
     * @return string
     */
    public function getSessionEmail()
    {
        $test = explode(';',$_SESSION['_sf2_attributes']['_security_main']);
        $email = strtolower(substr($test[29],6,-1));

        return $email;
    }

    /**
     * @param $ret
     * @return array
     */
    function listID ($ret)
    {
        $email = $this->getSessionEmail();
        $em = $this->getDoctrine()->getManager();
        $query_done_offered = $em->createQuery(
            'SELECT p.numticket, p.id
                    FROM SatisfactionFormBundle:Ticket p
                    WHERE p.email = :email
                    AND p.status = :status
                    ORDER BY p.numticket ASC'
        )->setParameters(array(
            'email' => $email,
            'status' => 'Offered',
        ));
        $offered = $query_done_offered->getResult();
        if($ret='1') $tab = array_column($offered, 'id');
        if($ret='2') $tab = array_column($offered, 'numticket');

        return $tab ;
    }

    /**
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function satupdateAction(Request $request)
    {

        if($request->request->get('ticket_type_new') != null)
        {
            $req_post = $request->request->get('ticket_type_new');
        }
        if($request->request->get('ticket_type_view') != null)
        {
            $req_post = $request->request->get('ticket_type_view');
        }
        if($request->request->get('ticket_type_edit') != null)
        {
            $req_post = $request->request->get('ticket_type_edit');
        }

        $ticket = $this->setTheTicket($req_post);

        if(isset($req_post['Envoyer'])) {

            $message= "Formulaire correctement enregistré";

            $form = $this->createForm(TicketTypeView::class, $ticket, array(
                'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
                'method' => 'POST',));

            return $this->render('SatisfactionFormBundle:Default:indexView.html.twig', array(
                'satisfactionForm' => $form->createView(),
                'message' => $message,
            ));
            exit;
        }
        if(isset($req_post['Modifier'])) {
            $form = $this->createForm(TicketTypeEdit::class, $ticket, array(
                'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
                'method' => 'POST',));

            return $this->render('SatisfactionFormBundle:Default:indexEdit.html.twig', array(
                'satisfactionForm' => $form->createView(),
            ));
            exit;
        }
    }

    /**
     * @param $numticket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction($numticket)
    {

        echo "<br><br><br><br>";

        if($numticket != '0')
        {

            $ticket = $this->getTheTicket($numticket);

            $form = $this->createForm(TicketType::class, $ticket, array(
                'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
                'method' => 'POST',));
        }
        else
        {
            $nt = $this->listID('2');
            $ticket = $this->getTheTicket($nt);

            $form = $this->createForm(TicketType::class, $ticket, array(
                'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
                'method' => 'POST',));

        }
        return $this->render('SatisfactionFormBundle:Default:index.html.twig', array(
            'satisfactionForm' => $form->createView(),
        ));

    }

    /**
     * @param $numticket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function viewAction($numticket)
    {
        if ($numticket== '0' OR !isset($numticket)) {
            return $this->render('SatisfactionGeneralBundle:Default:notickets.html.twig');
            exit;
        }

        $ticket = $this->getTheTicket($numticket);

        $form = $this->createForm(TicketTypeView::class, $ticket, array(
            'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
            'method' => 'POST',));

        return $this->render('SatisfactionFormBundle:Default:indexView.html.twig', array(
            'satisfactionForm' => $form->createView(),
        ));
    }

    /**
     * @param $numticket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function newAction($numticket)
    {
        if ($numticket== '0' OR !isset($numticket)) {
            return $this->render('SatisfactionGeneralBundle:Default:notickets.html.twig');
            exit;
        }

        $ticket = $this->getTheTicket($numticket);

        $form = $this->createForm(TicketTypeNew::class, $ticket, array(
            'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
            'method' => 'POST',));

        return $this->render('SatisfactionFormBundle:Default:indexNew.html.twig', array(
            'satisfactionForm' => $form->createView(),
        ));

    }

    /**
     * @param $numticket
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function editAction($numticket)
    {
        if ($numticket== '0' OR !isset($numticket)) {
            return $this->render('SatisfactionGeneralBundle:Default:notickets.html.twig');
            exit;
        }

        $ticket = $this->getTheTicket($numticket);

        $form = $this->createForm(TicketTypeEdit::class, $ticket, array(
            'action' => $this->generateUrl('satisfaction_form_homepage_satupdate'),
            'method' => 'POST',));

        return $this->render('SatisfactionFormBundle:Default:indexEdit.html.twig', array(
            'satisfactionForm' => $form->createView(),
        ));

    }
}
