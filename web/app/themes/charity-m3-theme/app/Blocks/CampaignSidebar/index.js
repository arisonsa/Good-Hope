/**
 * WordPress dependencies
 */
import { __ } from '@wordpress/i18n';
import { registerPlugin } from '@wordpress/plugins';
import { PluginSidebar, PluginSidebarMoreMenuItem } from '@wordpress/edit-post';
import {
    PanelBody,
    TextControl,
    TextareaControl,
    SelectControl,
    DateTimePicker,
    Button,
    Spinner,
} from '@wordpress/components';
import { useSelect, useDispatch } from '@wordpress/data';
import { useState } from '@wordpress/element';
import apiFetch from '@wordpress/api-fetch';
import { store as coreStore } from '@wordpress/core-data';

// Internal dependencies
const CPT_SLUG = 'newsletter_campaign';
const META_PREFIX = '_campaign_';

const CampaignSidebarPlugin = () => {
    // Get the current post type
    const postType = useSelect((select) => select('core/editor').getCurrentPostType(), []);

    // Get post meta and editor dispatch functions
    const { campaignMeta, isSaving } = useSelect((select) => {
        const editor = select('core/editor');
        return {
            campaignMeta: editor.getEditedPostAttribute('meta'),
            isSaving: editor.isSavingPost() || editor.isAutosavingPost(),
        };
    }, []);
    const { editPost, savePost } = useDispatch('core/editor');

    // State for various actions
    const [isSendingTest, setIsSendingTest] = useState(false);
    const [isSendingNow, setIsSendingNow] = useState(false);
    const [testEmailAddress, setTestEmailAddress] = useState('');
    const [actionResult, setActionResult] = useState({ message: '', type: '' });

    // Helper to update meta fields
    const updateMeta = (key, value) => {
        editPost({ meta: { ...campaignMeta, [`${META_PREFIX}${key}`]: value } });
    };

    // Get current admin email for test default
    const currentUser = useSelect((select) => select(coreStore).getCurrentUser(), []);
    useEffect(() => {
        if (currentUser && !testEmailAddress) {
            setTestEmailAddress(currentUser.email);
        }
    }, [currentUser]);


    // Send test email handler
    const handleSendTest = () => {
        if (!testEmailAddress) {
            setActionResult({ message: 'Please enter a test email address.', type: 'error' });
            return;
        }
        setIsSendingTest(true);
        setActionResult({ message: '', type: '' });

        const postId = select('core/editor').getCurrentPostId();

        apiFetch({
            path: `/charitym3/v1/campaigns/${postId}/send-test`,
            method: 'POST',
            data: { email: testEmailAddress },
        }).then((response) => {
            setActionResult({ message: response.message, type: 'success' });
            setIsSendingTest(false);
        }).catch((error) => {
            setActionResult({ message: error.message || 'Failed to send test email.', type: 'error' });
            setIsSendingTest(false);
        });
    };

    // Send Now handler
    const handleSendNow = () => {
        if (!window.confirm(__('Are you sure you want to send this campaign to all subscribers immediately? This action cannot be undone.', 'charity-m3'))) {
            return;
        }

        setIsSendingNow(true);
        setActionResult({ message: '', type: '' });
        const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);

        // Save the post first to ensure we send the latest content
        savePost().then(() => {
            apiFetch({
                path: `/charitym3/v1/campaigns/${postId}/send-now`,
                method: 'POST',
            }).then((response) => {
                setActionResult({ message: response.message, type: 'success' });
                // Refresh post data to get new status ('sending')
                dispatch('core/editor').refreshPost();
            }).catch((error) => {
                setActionResult({ message: error.message || 'Failed to start sending campaign.', type: 'error' });
            }).finally(() => {
                setIsSendingNow(false);
            });
        }).catch(() => {
            // Handle save error
            setActionResult({ message: 'Could not save post before sending. Please save manually and try again.', type: 'error' });
            setIsSendingNow(false);
        });
    };

    // Don't render the sidebar if we're not on the correct CPT
    if (postType !== CPT_SLUG) {
        return null;
    }

    const campaignStatus = campaignMeta[`${META_PREFIX}status`] || 'draft';
    const scheduledAt = campaignMeta[`${META_PREFIX}scheduled_at`];
    const isSentOrSending = ['sending', 'sent'].includes(campaignStatus);

    return (
        <>
            <PluginSidebarMoreMenuItem target="charity-m3-campaign-sidebar">
                {__('Campaign Settings (M3)', 'charity-m3')}
            </PluginSidebarMoreMenuItem>
            <PluginSidebar
                name="charity-m3-campaign-sidebar"
                title={__('Campaign Settings (M3)', 'charity-m3')}
            >
                <PanelBody title={__('Email Details', 'charity-m3')}>
                    <TextControl
                        label={__('Subject Line', 'charity-m3')}
                        value={campaignMeta[`${META_PREFIX}subject`] || ''}
                        onChange={(val) => updateMeta('subject', val)}
                        help={__('The subject line of the email campaign.', 'charity-m3')}
                        disabled={isSentOrSending}
                    />
                    <TextareaControl
                        label={__('Preheader Text', 'charity-m3')}
                        value={campaignMeta[`${META_PREFIX}preheader_text`] || ''}
                        onChange={(val) => updateMeta('preheader_text', val)}
                        help={__('A short summary text that follows the subject line when an email is viewed in the inbox.', 'charity-m3')}
                        rows={3}
                        disabled={isSentOrSending}
                    />
                </PanelBody>

                <PanelBody title={__('Status & Actions', 'charity-m3')}>
                     <SelectControl
                        label={__('Status', 'charity-m3')}
                        value={campaignStatus}
                        options={[
                            { label: 'Draft', value: 'draft' },
                            { label: 'Scheduled', value: 'scheduled' },
                            { label: 'Sending', value: 'sending', disabled: true },
                            { label: 'Sent', value: 'sent', disabled: true },
                            { label: 'Archived', value: 'archived' },
                        ]}
                        onChange={(val) => updateMeta('status', val)}
                        disabled={isSentOrSending}
                    />
                    {campaignStatus === 'scheduled' && (
                        <div style={{ marginTop: '1rem' }}>
                            <p><strong>{__('Scheduled For:', 'charity-m3')}</strong></p>
                            <DateTimePicker
                                currentDate={scheduledAt}
                                onChange={(val) => updateMeta('scheduled_at', val)}
                                is12Hour={true}
                                disabled={isSentOrSending}
                            />
                        </div>
                    )}
                    <div style={{ marginTop: '1rem' }}>
                        <Button
                            variant="primary"
                            onClick={handleSendNow}
                            isBusy={isSendingNow}
                            disabled={isSendingNow || isSaving || isSentOrSending}
                        >
                            {__('Send Now', 'charity-m3')}
                        </Button>
                        <p className="components-form-token-field__help">
                            {__('This will start sending the campaign to all subscribers immediately.', 'charity-m3')}
                        </p>
                    </div>
                </PanelBody>

                <PanelBody title={__('Testing', 'charity-m3')}>
                    <TextControl
                        label={__('Send Test Email To:', 'charity-m3')}
                        type="email"
                        value={testEmailAddress}
                        onChange={setTestEmailAddress}
                    />
                    <Button
                        variant="secondary"
                        onClick={handleSendTest}
                        isBusy={isSendingTest}
                        disabled={isSendingTest || isSaving}
                    >
                        {__('Send Test', 'charity-m3')}
                    </Button>
                </PanelBody>

                {/* Global feedback area */}
                <PanelBody>
                    {isSaving && <p><Spinner /> {__('Saving post...', 'charity-m3')}</p>}
                    {actionResult.message && (
                        <p style={{ color: actionResult.type === 'error' ? 'red' : 'green' }}>
                            {actionResult.message}
                        </p>
                    )}
                </PanelBody>
            </PluginSidebar>
        </>
    );
};

registerPlugin('charity-m3-campaign-sidebar', {
    render: CampaignSidebarPlugin,
    icon: 'email-alt', // Icon for the sidebar menu item
});
