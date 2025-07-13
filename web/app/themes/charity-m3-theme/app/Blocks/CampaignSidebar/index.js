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
    const { editPost } = useDispatch('core/editor');

    // State for test email sending
    const [isSendingTest, setIsSendingTest] = useState(false);
    const [testEmailAddress, setTestEmailAddress] = useState('');
    const [testSendResult, setTestSendResult] = useState({ message: '', type: '' });

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
            setTestSendResult({ message: 'Please enter a test email address.', type: 'error' });
            return;
        }
        setIsSendingTest(true);
        setTestSendResult({ message: '', type: '' });

        const postId = useSelect((select) => select('core/editor').getCurrentPostId(), []);

        apiFetch({
            path: `/charitym3/v1/campaigns/${postId}/send-test`,
            method: 'POST',
            data: { email: testEmailAddress },
        }).then((response) => {
            setTestSendResult({ message: response.message, type: 'success' });
            setIsSendingTest(false);
        }).catch((error) => {
            setTestSendResult({ message: error.message || 'Failed to send test email.', type: 'error' });
            setIsSendingTest(false);
        });
    };

    // Don't render the sidebar if we're not on the correct CPT
    if (postType !== CPT_SLUG) {
        return null;
    }

    const campaignStatus = campaignMeta[`${META_PREFIX}status`] || 'draft';
    const scheduledAt = campaignMeta[`${META_PREFIX}scheduled_at`];

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
                    />
                    <TextareaControl
                        label={__('Preheader Text', 'charity-m3')}
                        value={campaignMeta[`${META_PREFIX}preheader_text`] || ''}
                        onChange={(val) => updateMeta('preheader_text', val)}
                        help={__('A short summary text that follows the subject line when an email is viewed in the inbox.', 'charity-m3')}
                        rows={3}
                    />
                </PanelBody>

                <PanelBody title={__('Status & Scheduling', 'charity-m3')}>
                     <SelectControl
                        label={__('Status', 'charity-m3')}
                        value={campaignStatus}
                        options={[
                            { label: 'Draft', value: 'draft' },
                            { label: 'Scheduled', value: 'scheduled' },
                            // 'Sending' and 'Sent' are set by the system
                            { label: 'Sending', value: 'sending', disabled: true },
                            { label: 'Sent', value: 'sent', disabled: true },
                            { label: 'Archived', value: 'archived' },
                        ]}
                        onChange={(val) => updateMeta('status', val)}
                    />
                    {campaignStatus === 'scheduled' && (
                        <div>
                            <p><strong>{__('Scheduled For:', 'charity-m3')}</strong></p>
                            <DateTimePicker
                                currentDate={scheduledAt}
                                onChange={(val) => updateMeta('scheduled_at', val)}
                                is12Hour={true}
                            />
                        </div>
                    )}
                    {/* Add a "Send Now" button here if desired */}
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
                    {isSaving && <p><Spinner /> {__('Saving post...', 'charity-m3')}</p>}
                    {testSendResult.message && (
                        <p style={{ color: testSendResult.type === 'error' ? 'red' : 'green', marginTop: '10px' }}>
                            {testSendResult.message}
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
