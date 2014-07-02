<?php

namespace SaasOvation\Common\Port\Adapter\Messaging\RabbitMQ;

use DateTimeImmutable;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Exception;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use SaasOvation\Common\Port\Adapter\Messaging\MessageException;

/**
 * I am a message consumer, which facilitates receiving messages
 * from a Queue. A MessageListener or a client may close me,
 * terminating message consumption.
 *
 * @author Vaughn Vernon
 */
class MessageConsumer
{
    /**
     * My autoAcknowledged property.
     *
     * @var boolean
     */
    private $autoAcknowledged;

    /**
     * My closed property, which indicates I have been closed.
     *
     * @var boolean
     */
    private $closed;

    /**
     * My messageTypes, which indicates the messages of types I accept.
     *
     * @var Collection
     */
    private $messageTypes;

    /**
     * My queue, which is where my messages come from.
     *
     * @var Queue
     */
    private $queue;

    /**
     * My tag, which is produced by the broker.
     *
     * @var string
     */
    private $tag;

    /**
     * Answers a new auto-acknowledged MessageConsumer, which means all
     * messages received are automatically considered acknowledged as
     * received from the broker.
     *
     * @param Queue $aQueue the Queue from which messages are received
     *
     * @return MessageConsumer
     */
    public static function autoAcknowledgedInstance(Queue $aQueue)
    {
        return MessageConsumer::instance($aQueue, true);
    }

    /**
     * Answers a new MessageConsumer with acknowledgment managed per
     * isAutoAcknowledged.
     *
     * @param Queue $aQueue the Queue from which messages are received
     * @param boolean $isAutoAcknowledged the boolean indicating whether or not auto-acknowledgment is used
     *
     * @return MessageConsumer
     */
    public static function instance(Queue $aQueue, $isAutoAcknowledged = false)
    {
        return new MessageConsumer($aQueue, $isAutoAcknowledged);
    }

    /**
     * Closes me, which closes my queue.
     */
    public function close()
    {
        $this->setClosed(true);

        $this->queue()->close();
    }

    /**
     * Answers whether or not I have been closed.
     *
     * @return boolean
     */
    public function isClosed()
    {
        return $this->closed;
    }

    /**
     * Ensure an equalization of message distribution
     * across all consumers of $this queue.
     */
    public function equalizeMessageDistribution()
    {
        try {
            $this->queue()->channel()->basicQos(1);
        } catch (Exception $e) {
            throw new MessageException('Cannot equalize distribution.', $e);
        }
    }

    /**
     * Receives all messages on a separate thread and dispatches
     * them to aMessageListener until I am closed or until the
     * broker is shut down.
     *
     * @param MessageListener $aMessageListener the MessageListener that handles messages
     */
    public function receiveAll(MessageListener $aMessageListener)
    {
        $this->receiveFor($aMessageListener);
    }

    /**
     * Receives only messages of types included in aMessageTypes
     * on a separate thread and dispatches them to aMessageListener
     * until I am closed or until the broker is shut down. The type
     * must be included in the message's basic properties. If the
     * message's type is null, the message is filtered out.
     *
     * @param string[] $aMessageTypes the String[] indicating filtered message types
     * @param MessageListener $aMessageListener the MessageListener that handles messages
     */
    public function receiveOnly($aMessageTypes, $aMessageListener)
    {
        $filterOutAllBut = $aMessageTypes;

        if (null === $filterOutAllBut) {
            $filterOutAllBut = [];
        }

        $this->setMessageTypes(new ArrayCollection($filterOutAllBut));

        $this->receiveFor($aMessageListener);
    }

    /**
     * Answers my tag, which was produced by the broker.
     *
     * @return String
     */
    public function tag()
    {
        return $this->tag;
    }

    /**
     * Constructs my default state.
     *
     * @param Queue $aQueue the Queue from which I receive messages
     * @param boolean $isAutoAcknowledged the boolean indicating whether or not auto-acknowledgment is used
     */
    protected function __construct(Queue $aQueue, $isAutoAcknowledged)
    {
        $this->setMessageTypes(new ArrayCollection());

        $this->setQueue($aQueue);

        $this->setAutoAcknowledged($isAutoAcknowledged);
    }

    /**
     * Answers my autoAcknowledged.
     *
     * @return boolean
     */
    private function isAutoAcknowledged()
    {
        return $this->autoAcknowledged;
    }

    /**
     * Sets my autoAcknowledged.
     *
     * @param boolean $isAutoAcknowledged the boolean to set as my autoAcknowledged
     */
    private function setAutoAcknowledged($isAutoAcknowledged)
    {
        $this->autoAcknowledged = $isAutoAcknowledged;
    }

    /**
     * Sets my closed.
     *
     * @param boolean $aClosed the boolean to set as my closed
     */
    private function setClosed($aClosed)
    {
        $this->closed = $aClosed;
    }

    /**
     * Answers my queue.
     *
     * @return Queue
     */
    protected function queue()
    {
        return $this->queue;
    }

    /**
     * Answers my messageTypes.
     *
     * @return Collection
     */
    private function messageTypes()
    {
        return $this->messageTypes;
    }

    /**
     * Registers aMessageListener with the channel indirectly using
     * a DispatchingConsumer.
     *
     * @param MessageListener $aMessageListener the MessageListener
     */
    private function receiveFor(MessageListener $aMessageListener)
    {
        $queue = $this->queue();
        $channel = $queue->channel();

        try {
            $tag = $channel->basic_consume(
                $queue->name(),
                '',
                false,
                !$this->isAutoAcknowledged(),
                false,
                false,
                function(AMQPMessage $aMessage) use ($aMessageListener) {
                    $aMessageListener->handleMessage(
                        $aMessage->get('type'),
                        $aMessage->has('message_id') ? $aMessage->get('message_id') : null,
                        $aMessage->has('timestamp') ? (new DateTimeImmutable())->setTimestamp($aMessage->get('timestamp')) : null,
                        $aMessage->body,
                        $aMessage->delivery_info['delivery_tag'],
                        $aMessage->delivery_info['redelivered']
                    );
                }
            );

            $this->setTag($tag);

        } catch (Exception $e) {
            throw new MessageException("Failed to initiate consumer.", e);
        }
    }

    /**
     * Sets my messageTypes.
     * 
     * @param Collection aMessageTypes the Set<String> to set as my messageTypes
     */
    private function setMessageTypes(Collection $aMessageTypes)
    {
        $this->messageTypes = $aMessageTypes;
    }

    /**
     * Sets my queue.
     * 
     * @param Queue $aQueue the Queue to set as my queue
     */
    private function setQueue(Queue $aQueue)
    {
        $this->queue = $aQueue;
    }

    /**
     * Sets my tag.
     * 
     * @param string $aTag the String to set as my tag
     */
    private function setTag($aTag)
    {
        $this->tag = $aTag;
    }
}

/*class DispatchingConsumer extends DefaultConsumer
{
    private $messageListener;

    public function __construct(AMQPChannel $aChannel, MessageListener $aMessageListener)
    {
    super(aChannel);

    $this->setMessageListener(aMessageListener);
    }

@Override
        public void handleDelivery(
    String aConsumerTag,
                Envelope anEnvelope,
                BasicProperties aProperties,
                byte[] aBody) throws IOException {

    if (!isClosed()) {
        handle($this->messageListener(), new Delivery(anEnvelope, aProperties, aBody));
    }

    if (isClosed()) {
        queue()->close();
    }
}

        @Override
        public void handleShutdownSignal(
    String aConsumerTag,
                ShutdownSignalException aSignal) {

    close();
}

        private void handle(
    MessageListener aMessageListener,
                Delivery aDelivery) {
    try {
        if ($this->filteredMessageType(aDelivery)) {
            ;
        } else if (aMessageListener->type()->isBinaryListener()) {
            aMessageListener
            ->handleMessage(
                aDelivery->getProperties()->getType(),
                aDelivery->getProperties()->getMessageId(),
                aDelivery->getProperties()->getTimestamp(),
                aDelivery->getBody(),
                aDelivery->getEnvelope()->getDeliveryTag(),
                aDelivery->getEnvelope()->isRedeliver());
        } else if (aMessageListener->type()->isTextListener()) {
            aMessageListener
            ->handleMessage(
                aDelivery->getProperties()->getType(),
                aDelivery->getProperties()->getMessageId(),
                aDelivery->getProperties()->getTimestamp(),
                new String(aDelivery->getBody()),
                aDelivery->getEnvelope()->getDeliveryTag(),
                aDelivery->getEnvelope()->isRedeliver());
        }

        $this->ack(aDelivery);

    } catch (MessageException e) {
        // System->out->println("MESSAGE EXCEPTION (MessageConsumer): " + e->getMessage());
        $this->nack(aDelivery, e->isRetry());
    } catch (Throwable t) {
        // System->out->println("EXCEPTION (MessageConsumer): " + t->getMessage());
        $this->nack(aDelivery, false);
    }
        }

        private void ack(Delivery aDelivery) {
    try {
        if (!isAutoAcknowledged()) {
            $this->getChannel()->basicAck(
                aDelivery->getEnvelope()->getDeliveryTag(),
                false);
        }
    } catch (IOException ioe) {
        // fall through
    }
        }

        private void nack(Delivery aDelivery, boolean isRetry) {
    try {
        if (!isAutoAcknowledged()) {
            $this->getChannel()->basicNack(
                aDelivery->getEnvelope()->getDeliveryTag(),
                false,
                isRetry);
        }
    } catch (IOException ioe) {
        // fall through
    }
        }

        private boolean filteredMessageType(Delivery aDelivery) {
    boolean filtered = false;

            Set<String> filteredMessageTypes = messageTypes();

            if (!filteredMessageTypes->isEmpty()) {
                String messageType = aDelivery->getProperties()->getType();

                if (messageType == null || !filteredMessageTypes->contains(messageType)) {
                    filtered = true;
                }
            }

            return filtered;
        }

        private MessageListener messageListener() {
            return messageListener;
        }

        private void setMessageListener(MessageListener messageListener) {
    $this->messageListener = messageListener;
}
    }*/
